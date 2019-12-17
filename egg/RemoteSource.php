<?php

require_once 'Utils.php';
require_once 'EGGDatabase.php';

interface IRemoteSource
{
	/** @param $db EGGDatabase */
	public function update(EGGDatabase $db);

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
	const API_COMMITSLIST      = 'https://api.github.com/repos/{repo}/commits?per_page=100&sha={sha}';
	const API_BRANCHLIST       = 'https://api.github.com/repos/{repo}/branches';

	/** @var ILogger $logger */
	private $logger;

	/** @var string $name */
	private $name;

	/** @var string $url */
	private $url;

	/** @var string $filter */
	private $filter;

	/** @var string[] exclusions */
	private $exclusions;

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
	 * @param string $name
	 * @param string $url
	 * @param string $filter
	 * @param string[] exclusions
	 * @param string $oauth_id
	 * @param string $oauth_secret
	 * @param string $apitokenpath
	 */
	public function __construct(ILogger $logger, string $name, string $url, string $filter, array $exclusions, string $oauth_id, string $oauth_secret, string $apitokenpath)
	{
		$this->logger       = $logger;
		$this->name         = $name;
		$this->url          = $url;
		$this->filter       = $filter;
		$this->exclusions   = $exclusions;
		$this->oauth_id     = $oauth_id;
		$this->oauth_secret = $oauth_secret;
		$this->apitokenpath = $apitokenpath;

		if ($this->apitokenpath !== null && file_exists($this->apitokenpath))
			$this->apitoken = file_get_contents($this->apitokenpath);
		else
			$this->apitoken = null;
	}

	/**
	 * @throws Exception
	 */
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

	/** @inheritDoc
	 * @throws Exception
	 */
	public function update(EGGDatabase $db) {
		if ($this->apitoken === null) $this->queryAPIToken();

		$repos = $this->listAndUpdateRepositories($db);

		foreach ($repos as $repo)
		{
			$branches = $this->listAndUpdateBranches($db, $repo);
			$db->setUpdateDateOnRepository($repo);

			$repo_changed = false;
			foreach ($branches as $branch)
			{
				if ($branch->HeadFromAPI === $branch->Head)
				{
					$db->setUpdateDateOnBranch($branch);
					$this->logger->proclog("Branch: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "] is up to date");
					continue;
				}

				$commits = $this->listAndUpdateCommits($db, $repo, $branch);
				$db->setUpdateDateOnBranch($branch);
				if (count($commits) === 0)
				{
					$this->logger->proclog("Branch: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "] has no new commits");
					continue;
				}

				$this->logger->proclog("Found " . count($commits) . " new commits in Branch: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "]");

				$repo_changed = true;
				$db->setChangeDateOnBranch($branch);
			}

			if ($repo_changed) $db->setChangeDateOnRepository($repo);
		}
	}

	/**
	 * @param EGGDatabase $db
	 * @return Repository[]
	 * @throws Exception
	 */
	private function listAndUpdateRepositories(EGGDatabase $db) {
		$f = explode('/', $this->filter);

		$result = [];

		$page = 1;
		$url = Utils::sharpFormat(self::API_REPOSITORIESLIST, ['user'=>$f[0], 'page'=>$page]);

		$json = Utils::getJSON($this->logger, $url, $this->apitoken);

		while (! empty($json))
		{
			foreach ($json as $result_repo)
			{
				if (!Utils::isRepoFilterMatch($this->filter, $this->exclusions, $result_repo->{'full_name'})) continue;

				$this->logger->proclog("Found Repo in Remote: " . $result_repo->{'full_name'});

				$result []= $db->getOrCreateRepository($result_repo->{'html_url'}, $result_repo->{'full_name'}, $this->name);
			}

			$page++;
			$url = Utils::sharpFormat(self::API_REPOSITORIESLIST, ['user'=>$f[0], 'page'=>$page]);
			$json = Utils::getJSON($this->logger, $url, $this->apitoken);
		}

		$db->deleteOtherRepositories($this->name, $result);

		return $result;
	}

	/**
	 * @param EGGDatabase $db
	 * @param Repository $repo
	 * @return Branch[]
	 * @throws Exception
	 */
	private function listAndUpdateBranches(EGGDatabase $db, Repository $repo) {

		$url = Utils::sharpFormat(self::API_BRANCHLIST, ['repo' => $repo->Name]);

		$result = [];

		$json = Utils::getJSON($this->logger, $url, $this->apitoken);
		foreach ($json as $result_branch) {
			if (isset($json->{'block'})) continue;

			$bname = $result_branch->{'name'};
			$bhead = $result_branch->{'commit'}->{'sha'};

			$this->logger->proclog("Found Branch in Remote: [" . $repo->Name . "] " . $bname);

			$b = $db->getOrCreateBranch($this->name, $repo, $bname);
			$b->HeadFromAPI = $bhead;
			$result []= $b;
		}

		$db->deleteOtherBranches($this->name, $repo, $result);

		return $result;
	}

	/**
	 * @param EGGDatabase $db
	 * @param Repository $repo
	 * @param Branch $branch
	 * @return Commit[]
	 * @throws Exception
	 */
	private function listAndUpdateCommits(EGGDatabase $db, Repository $repo, Branch $branch) {

		$newcommits = [];

		if ($branch->HeadFromAPI === null) return [];

		$target = $branch->Head;

		$next_sha = [ $branch->HeadFromAPI ];
		$visited = [ $branch->HeadFromAPI ];

		$url = Utils::sharpFormat(self::API_COMMITSLIST, [ 'repo'=>$repo->Name, 'sha'=>$next_sha[0] ]);
		$this->logger->proclog("Query commits from: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "] starting at {" . substr($next_sha[0], 0, 8) . "}");
		$json = Utils::getJSON($this->logger, $url, $this->apitoken);
		for (;;)
		{
			foreach ($json as $result_commit)
			{
				$sha             = $result_commit->{'sha'};
				$author_name     = $result_commit->{'commit'}->{'author'}->{'name'};
				$author_email    = $result_commit->{'commit'}->{'author'}->{'email'};
				$committer_name  = $result_commit->{'commit'}->{'committer'}->{'name'};
				$committer_email = $result_commit->{'commit'}->{'committer'}->{'email'};
				$message         = $result_commit->{'commit'}->{'message'};
				$date            = (new DateTime($result_commit->{'commit'}->{'author'}->{'date'}))->format("Y-m-d H:i:s");

				$parents = array_map(function ($v){ return $v->{'sha'}; }, $result_commit->{'parents'});

				if (($rmshakey = array_search($sha, $next_sha)) !== false) unset($next_sha[$rmshakey]);

				if (in_array($sha, $visited)) continue;
				$visited []= $sha;

				if ($sha === $target && count($next_sha) === 0)
				{
					if (count($newcommits) === 0)
					{
						$this->logger->proclog("Found no new commits for: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "]  (HEAD at {" . substr($next_sha[0], 0, 8) . "})");
						return [];
					}

					$db->insertNewCommits($this->name, $repo, $branch, $newcommits);
					$db->setBranchHead($branch, $branch->HeadFromAPI);

					return $newcommits;
				}

				$commit = new Commit();
				$commit->Branch         = $branch;
				$commit->Hash           = $sha;
				$commit->AuthorName     = $author_name;
				$commit->AuthorEmail    = $author_email;
				$commit->CommitterName  = $committer_name;
				$commit->CommitterEmail = $committer_email;
				$commit->Message        = $message;
				$commit->Date           = $date;
				$commit->Parents        = $parents;

				$newcommits []= $commit;

				foreach ($parents as $p)
				{
					$next_sha []= $p;
				}
			}

			$next_sha = array_values($next_sha); // fix numeric keys
			if (count($next_sha) === 0) break;

			$url = Utils::sharpFormat(self::API_COMMITSLIST, [ 'repo'=>$repo->Name, 'sha'=>$next_sha[0] ]);
			$this->logger->proclog("Query commits from: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "] continuing at {" . substr($next_sha[0], 0, 8) . "}");
			$json = Utils::getJSON($this->logger, $url, $this->apitoken);
		}

		$this->logger->proclog("HEAD pointer in Branch: [" . $this->name . "|" . $repo->Name . "|" . $branch->Name . "] no longer matches. Re-query all " . count($newcommits) . " commits (old HEAD := {".substr($branch->Head, 0, 8)."})");

		$db->deleteAllCommits($branch);

		if (count($newcommits) === 0) return [];

		$db->insertNewCommits($this->name, $repo, $branch, $newcommits);
		$db->setBranchHead($branch, $branch->HeadFromAPI);

		return $newcommits;
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

	/** @var string[] exclusions */
	private $exclusions;

	/** @var string $username */
	private $username;

	/** @var string $password */
	private $password;

	/**
	 * @param ILogger $logger
	 * @param string $name
	 * @param string $url
	 * @param string $filter
	 * @param string[] $exclusions
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(ILogger $logger, string $name, string $url, string $filter, array $exclusions, string $username, string $password)
	{
		$this->logger       = $logger;
		$this->name         = $name;
		$this->url          = $url;
		$this->filter       = $filter;
		$this->exclusions   = $exclusions;
		$this->username     = $username;
		$this->password     = $password;
	}

	/** @inheritDoc  */
	public function update(EGGDatabase $db)
	{
		// TODO: Implement update() method.
	}

	/** @inheritDoc  */
	public function getName() { return $this->name; }

	/** @inheritDoc  */
	public function toString() { return "[Gitea|".$this->url."|".$this->filter."]"; }
}