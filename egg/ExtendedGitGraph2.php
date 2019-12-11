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
		if ($config['output_session']) $this->logger []= new SessionLogger($config['session_var']);
		if ($config['output_stdout'])  $this->logger []= new OutputLogger();
		if ($config['output_logfile']) $this->logger []= new FileLogger($config['logfile'], $config['logfile_count']);

		$this->sources = [];
		foreach ($config['remotes'] as $rmt)
		{
			if ($rmt['type'] === 'github')
				$this->sources []= new GithubConnection($this, $rmt['url'], $rmt['filter'], $rmt['oauth_id'], $rmt['oauth_secret'], $rmt['token_cache'] );
			else if ($rmt['type'] === 'gitea')
				$this->sources []= new GiteaConnection($this, $rmt['url'], $rmt['filter'], $rmt['username'], $rmt['password'] );
			else
				throw new Exception("Unknown remote-type: " . $rmt['type']);
		}
	}

	public function update()
	{
		try {
			$this->proclog("Start incremental data update");
			$this->proclog();

			foreach ($this->sources as $src)
			{
				$this->proclog("======= UPDATE " . $src->getName() . " =======");

				$src->update();

				$this->proclog();
			}

			$this->proclog("Update finished.");
		} catch (Exception $exception) {

			$this->proclog("(!) FATAL ERROR -- UNCAUGHT EXCEPTION THROWN");
			$this->proclog();
			$this->proclog($exception->getMessage());
			$this->proclog();
			$this->proclog($exception->getTraceAsString());
		}
	}

	public function proclog($text = '')
	{
		foreach($this->logger as $lgr) $lgr->proclog($text);
	}
}