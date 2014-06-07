<?php

/* https://api.github.com/rate_limit
 * https://api.github.com/users/Mikescher/repos?page=1&per_page=100
 * https://api.github.com/repos/Mikescher/BefunGen/commits?author=mailport@mikescher.de&sha=3498a7d04dfec2775eb8cc12fdb856aea4d08184
 *
 */

class ExtendedGitGraph {

	const API_AUTHORIZE = 'https://github.com/login/oauth/authorize?client_id=d51cb5eb4036e5b5b871';
	const API_TOKEN = 'https://github.com/login/oauth/access_token?client_id=d51cb5eb4036e5b5b871&client_secret=536915cfd90f2d3a501fbde25fc1965a24523421&code=%s';

	const API_RATELIMIT = 'https://api.github.com/rate_limit';
	const API_REPOSITORIESLIST = 'https://api.github.com/users/%s/repos?page=%d&per_page=100';

	private $username;
	private $token;
	private $tokenHeader;

	private $repositories;
	private $commits;

	public function __construct($usr_name) {
		$this->username = $usr_name;

		set_time_limit(300); // 5min
	}

	public function authenticate($auth_key) {
		$url = sprintf(self::API_TOKEN, $auth_key);
		$result = file_get_contents($url);

		$result = str_replace('access_token=', '', $result);
		$result = str_replace('&scope=&token_type=bearer', '', $result);

		setToken($result);
	}

	public function setToken($token) {
		$this->token = $token;
		$this->tokenHeader = 'access_token=' . $token . '&token_type=bearer';
	}

	public function collect() {
		ob_implicit_flush(true);
		ob_end_flush();

		//--------------------------------

		$this->listRepositories();

		$this->listAllCommits();

		$this->save();

		//--------------------------------

		$this->output_flushed($this->getRemainingRequests() . ' Requests remaining');
	}

	private function listRepositories() {
		$page = 1;
		$url = sprintf(self::API_REPOSITORIESLIST . '&' . $this->tokenHeader, $this->username, $page);

		$result = $this->getJSON($url);

		$repo_list = array();

		while (! empty($result)) {
			foreach ($result as $result_repo) {
				$repo_list[] = $this->parseRepoJSON($result_repo);

				$this->output_flushed("Found Repo: " . $result_repo->{'full_name'});
			}

			//##########

			$url = sprintf(self::API_REPOSITORIESLIST . '&' . $this->tokenHeader, $this->username, ++$page);

			$result = $this->getJSON($url);
		}

		$this->repositories = $repo_list;
	}

	private function getJSON($url) {
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
//		$options  = array('http' => array('user_agent'=> 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36'));
		$context  = stream_context_create($options);
		$response = file_get_contents($url, false, $context);

		return json_decode($response);
	}

	private function getRemainingRequests() {
		$json = $this->getJSON(self::API_RATELIMIT . '?' . $this->tokenHeader);

		return $json->{'resources'}->{'core'}->{'remaining'};
	}

	private function listAllCommits() {
		$this->commits = array();

		foreach($this->repositories as $repo) {
			$this->listCommits($repo);
		}
	}

	private function listCommits($repo) {
		$page = 1;
		$url = $repo['commits_url'] . '?per_page=100&page=' . $page . '&author=' .$this->username . '&' .$this->tokenHeader;

		$result = $this->getJSON($url);

		$commit_list = array();

		while (! empty($result)) {
			foreach ($result as $result_commit) {
				$commit_list[] = $this->parseCommitJSON($repo, $result_commit);
			}

			$this->output_flushed("Found 100 Commits from  " . $repo['full_name']);

			//##########

			$url = $repo['commits_url'] . '?per_page=100&page=' . ++$page . '&author=' .$this->username . '&' .$this->tokenHeader;

			$result = $this->getJSON($url);
		}

		$this->commits = array_merge($this->commits, $commit_list);
	}

	private function parseRepoJSON($json) {
		return
			[
				'id' => $json->{'id'},
				'name' => $json->{'name'},
				'full_name' => $json->{'full_name'},
				'owner' => $json->{'owner'}->{'login'},
				'owner_id' => $json->{'owner'}->{'id'},
				'owner_avatar-url' => $json->{'owner'}->{'avatar_url'},
				'owner_gravatar-id' => $json->{'owner'}->{'gravatar_id'},
				'url' => $json->{'html_url'},
				'language' => $json->{'language'},
				'url' => $json->{'html_url'},
				'creation' => DateTime::createFromFormat(DateTime::ISO8601, $json->{'created_at'}),
				'size' => $json->{'size'},
				'default_branch' => $json->{'default_branch'},
				'commits_url' => str_replace('{/sha}', '', $json->{'commits_url'}),
			];
	}

	private function parseCommitJSON($repo, $json) {
		return
			[
				'sha' => $json->{'sha'},
				'author_name' => $json->{'commit'}->{'author'}->{'name'},
				'author_mail' => $json->{'commit'}->{'author'}->{'email'},
				'author_login' => $json->{'author'}->{'login'},
				'author_id' => $json->{'author'}->{'id'},
				'sha' => $json->{'sha'},
				'message' => $json->{'commit'}->{'message'},
				'repository' => $repo,
				'date' => DateTime::createFromFormat(DateTime::ISO8601, $json->{'commit'}->{'author'}->{'date'}),
			];
	}

	private function save() {
		$this->output_flushed("Start saving data");

		$save = serialize(
			[
				'repositories' => $this->repositories,
				'commits' => $this->commits,
			]);

		file_put_contents('ext_git_graph.dat', $save);

		$this->output_flushed('Finished saving data');
	}

	private function output_flushed($txt)
	{
		echo '[' . date('H:i.s') . '] ' . $txt . "<br>";
	}

	public function load() {
		$data = unserialize(file_get_contents('ext_git_graph.dat'));

		$this->repositories = $data['repositories'];
		$this->commits = $data['commits'];
	}
} 