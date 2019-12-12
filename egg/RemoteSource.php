<?php

require_once 'Utils.php';
require_once 'EGGDatabase.php';

interface IRemoteSource
{
	/** @param $db EGGDatabase */
	public function update($db);

	/** @return string **/
	public function getName();

	/** @return string **/
	public function toString();
}

class GithubConnection implements IRemoteSource
{
	const API_OAUTH_AUTH  = 'https://github.com/login/oauth/authorize?client_id=%s';
	const URL_OAUTH_TOKEN = 'https://github.com/login/oauth/access_token?client_id={id}&client_secret={secret}&code={code}';

	const API_RATELIMIT        = 'https://api.github.com/rate_limit';
	const API_REPOSITORIESLIST = 'https://api.github.com/users/{user}/repos?page={page}&per_page=100';
	const API_COMMITSLIST      = 'https://api.github.com/repos/{repo}/commits?per_page=100&page={page}&author={author}';

	/** @var ILogger $logger */
	private $logger;

	/** @var string $name */
	private $name;

	/** @var string $url */
	private $url;

	/** @var string $filter */
	private $filter;

	/** @var string $oauth_id */
	private $oauth_id;

	/** @var string $oauth_secret */
	private $oauth_secret;

	/** @var string $apitokenpath */
	private $apitokenpath;

	/** @var string $apitoken */
	private $apitoken;

	/**
	 * @param ILogger $logger
	 * @param string $url
	 * @param string $filter
	 * @param string $oauth_id
	 * @param string $oauth_secret
	 * @param string $apitokenpath
	 */
	public function __construct($logger, $name, $url, $filter, $oauth_id, $oauth_secret, $apitokenpath)
	{
		$this->logger       = $logger;
		$this->name         = $name;
		$this->url          = $url;
		$this->filter       = $filter;
		$this->oauth_id     = $oauth_id;
		$this->oauth_secret = $oauth_secret;
		$this->apitokenpath = $apitokenpath;

		if ($this->apitokenpath !== null && file_exists($this->apitokenpath))
			$this->apitoken = file_get_contents($this->apitokenpath);
		else
			$this->apitoken = null;
	}

	public function queryAPIToken() {
		$url = Utils::sharpFormat(self::URL_OAUTH_TOKEN, ['id'=>$this->oauth_id, 'secret'=>$this->oauth_secret, 'code'=>'egg']);
		$fullresult = $result = file_get_contents($url);

		$result = str_replace('access_token=', '', $result);
		$result = str_replace('&scope=&token_type=bearer', '', $result);

		$this->logger->proclog("Updated Github API token");

		if (Utils::startsWith($result, "error=")) throw new Exception($fullresult);

		if ($result!=='' && $result !== null && $this->apitokenpath !== null)
			file_put_contents($this->apitokenpath, $result);

		$this->apitoken = $result;
	}

	/** @inheritDoc  */
	public function update($db) {
		if ($this->apitoken === null) $this->queryAPIToken();

		$repos = $this->listRepositories($db);
	}

	/**
	 * @param $db EGGDatabase
	 * @return Repository[]
	 */
	public function listRepositories($db) {
		$f = explode('/', $this->filter);

		$result = [];

		$page = 1;
		$url = Utils::sharpFormat(self::API_REPOSITORIESLIST, ['user'=>$f[0], 'page'=>$page, 'token'=>$this->apitoken]);

		$json = Utils::getJSON($this->logger, $url, $this->apitoken);

		while (! empty($json))
		{
			foreach ($json as $result_repo)
			{
				if (!Utils::isRepoFilterMatch($this->filter, $result_repo->{'full_name'})) continue;

				$this->logger->proclog("Found Repo in Remote: " . $result_repo->{'full_name'});

				$result []= $db->getOrCreateRepository($result_repo->{'html_url'}, $result_repo->{'full_name'}, $this->name);
			}

			$page++;
			$url = Utils::sharpFormat(self::API_REPOSITORIESLIST, ['user'=>$f[0], 'page'=>$page, 'token'=>$this->apitoken]);
			$json = Utils::getJSON($this->logger, $url, $this->apitoken);
		}

		$db->deleteOtherRepositories($this->name, $result);

		return $result;
	}

	/** @inheritDoc  */
	public function getName() { return $this->name; }

	/** @inheritDoc  */
	public function toString() { return "[Github|".$this->filter."]"; }
}

class GiteaConnection implements IRemoteSource
{
	/** @var ILogger $logger */
	private $logger;

	/** @var string $name */
	private $name;

	/** @var string $url */
	private $url;

	/** @var string $filter */
	private $filter;

	/** @var string $username */
	private $username;

	/** @var string $password */
	private $password;

	/**
	 * @param ILogger $logger
	 * @param string $name
	 * @param string $url
	 * @param string $filter
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($logger, $name, $url, $filter, $username, $password)
	{
		$this->logger       = $logger;
		$this->name         = $name;
		$this->url          = $url;
		$this->filter       = $filter;
		$this->username     = $username;
		$this->password     = $password;
	}

	/** @inheritDoc  */
	public function update($db)
	{
		// TODO: Implement update() method.
	}

	/** @inheritDoc  */
	public function getName() { return $this->name; }

	/** @inheritDoc  */
	public function toString() { return "[Gitea|".$this->url."|".$this->filter."]"; }
}