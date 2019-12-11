<?php

require_once 'Utils.php';

interface IRemoteSource
{
	public function update();

	/** @return string **/
	public function getName();
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
	public function __construct($logger, $url, $filter, $oauth_id, $oauth_secret, $apitokenpath)
	{
		$this->logger       = $logger;
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

	public function update() {
		if ($this->apitoken === null) $this->queryAPIToken();

		$repos = $this->listRepositories();
	}

	public function listRepositories() {
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

				$result []= $result_repo->{'full_name'};
				$this->logger->proclog("Found Repo: " . $result_repo->{'full_name'});
			}

			$page++;
			$url = Utils::sharpFormat(self::API_REPOSITORIESLIST, ['user'=>$f[0], 'page'=>$page, 'token'=>$this->apitoken]);
			$json = Utils::getJSON($this->logger, $url, $this->apitoken);
		}

		return $result;
	}

	/** @inheritDoc  */
	public function getName() { return "[Github|".$this->filter."]"; }
}

class GiteaConnection implements IRemoteSource
{
	/** @var ILogger $logger */
	private $logger;

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
	 * @param string $url
	 * @param string $filter
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($logger, $url, $filter, $username, $password)
	{
		$this->logger       = $logger;
		$this->url          = $url;
		$this->filter       = $filter;
		$this->username     = $username;
		$this->password     = $password;
	}

	public function update()
	{
		// TODO: Implement update() method.
	}

	/** @inheritDoc  */
	public function getName() { return "[Gitea|".$this->url."|".$this->filter."]"; }
}