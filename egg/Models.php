<?php

class Repository
{
	/** @var int */
	public $ID;

	/** @var string */
	public $URL;

	/** @var string */
	public $Name;

	/** @var string */
	public $Source;

	/** @var string */
	public $LastUpdate;

	/** @var string */
	public $LastChange;
}

class Branch
{
	/** @var int */
	public $ID;

	/** @var string */
	public $Name;

	/** @var Repository */
	public $Repo;

	/** @var string */
	public $Head;

	/** @var string */
	public $HeadFromAPI = null;

	/** @var string */
	public $LastUpdate;

	/** @var string */
	public $LastChange;
}

class Commit
{

}