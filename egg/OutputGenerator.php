<?php

require_once 'Utils.php';
require_once 'EGGDatabase.php';

interface IOutputGenerator
{
	/**
	 * @param $db EGGDatabase
	 * @return string
	 */
	public function generate(EGGDatabase $db): string;

	/**
	 * @return string|null
	 */
	public function loadFromCache();
}

class FullRenderer implements IOutputGenerator
{
	/** @var ILogger $logger */
	private $logger;

	/** @var string[] $identities */
	private $identities;

	/** @var string $cache_files_path */
	private $cache_files_path;

	/**
	 * @param ILogger $logger
	 * @param string[] $identities
	 * @param string $cfpath
	 */
	public function __construct(ILogger $logger, array $identities, string $cfpath)
	{
		$this->logger           = $logger;
		$this->identities       = $identities;
		$this->cache_files_path = $cfpath;
	}

	/**
	 * @param $db EGGDatabase
	 * @return string
	 */
	public function updateCache(EGGDatabase $db)
	{
		$years = $db->getAllYears();

		foreach ($years as $year)
		{
			$gen = new SingleYearRenderer($this->logger, $year, $this->identities, $this->cache_files_path);
			$gen->updateCache($db);
		}

		$data = json_encode($years);

		$path = Utils::sharpFormat($this->cache_files_path, ['ident' => 'fullrenderer']);

		file_put_contents($path, $data);
		$this->logger->proclog("Updated cache file for full renderer");
	}

	/**
	 * @inheritDoc
	 */
	public function loadFromCache()
	{
		$path = Utils::sharpFormat($this->cache_files_path, ['ident' => 'fullrenderer']);
		if (!file_exists($path))
		{
			$this->logger->proclog("No cache found for [fullrenderer]");
			return null;
		}

		$years = json_decode(file_get_contents($path));

		$result = "";

		foreach ($years as $year)
		{
			$gen = new SingleYearRenderer($this->logger, $year, $this->identities, $this->cache_files_path);
			$cc = $gen->loadFromCache();
			if ($cc === null) return null;
			$result .= $cc;
			$result .= "\n\n\n";
		}
	}
}

class SingleYearRenderer implements IOutputGenerator
{
	/** @var ILogger $logger */
	private $logger;

	/** @var int $year */
	private $year;

	/** @var string[] $identities */
	private $identities;

	/** @var string $cache_files_path */
	private $cache_files_path;

	/**
	 * @param ILogger $logger
	 * @param int $year
	 * @param string[] $identities
	 * @param string $cfpath
	 */
	public function __construct(ILogger $logger, int $year, array $identities, string $cfpath)
	{
		$this->logger           = $logger;
		$this->year             = $year;
		$this->identities       = $identities;
		$this->cache_files_path = $cfpath;
	}

	/**
	 * @inheritDoc
	 */
	public function loadFromCache()
	{
		$path = Utils::sharpFormat($this->cache_files_path, ['ident' => 'singleyear_'.$this->year]);
		if (!file_exists($path))
		{
			$this->logger->proclog("No cache found for [".('singleyear_'.$this->year)."]");
			return null;
		}

		return file_get_contents($path);
	}

	/**
	 * @inheritDoc
	 */
	public function updateCache(EGGDatabase $db)
	{
		$data = $this->generate($db);

		$path = Utils::sharpFormat($this->cache_files_path, ['ident' => 'singleyear_'.$this->year]);

		file_put_contents($path, $data);
		$this->logger->proclog("Updated cache file for year ".$this->year);
	}

	/**
	 * @inheritDoc
	 */
	private function generate(EGGDatabase $db): string
	{
		$dbdata = $db->getCommitCountOfYearByDate($this->year, $this->identitites);
	}
}