<?php

require_once 'Logger.php';
require_once 'RemoteSource.php';
require_once 'Utils.php';

class ExtendedGitGraph2 implements ILogger
{
	/* @var ILogger[] */
	private $logger;

	/* @var IRemoteSource[] */
	private $sources;

	public function __construct($config)
	{
		$this->logger = [];
		if ($config['output_stdout'])  $this->logger []= new OutputLogger();
		if ($config['output_logfile']) $this->logger []= new FileLogger($config['logfile'], $config['logfile_count']);
		if ($config['output_session']) $this->logger []= new SessionLogger($config['session_var']);

		$this->sources = [];
		foreach ($config['remotes'] as $rmt)
		{
			if ($rmt['type'] === 'github')
				$this->sources []= new GithubConnection($this, $rmt['url'], $rmt['filter'], $rmt['oauth_id'], $rmt['oauth_secret'], $rmt['token_cache'] );
			else if ($rmt['type'] === 'github')
				$this->sources []= new GiteaConnection($this, $rmt['url'], $rmt['filter'], $rmt['username'], $rmt['password'] );
			else
				throw new Exception("Unknown remote-type: " . $rmt['type']);
		}
	}

	public function log($text)
	{
		foreach($this->logger as $lgr) $lgr->log($text);
	}
}