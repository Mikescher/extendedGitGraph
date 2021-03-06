<?php

$cfg = require (__DIR__.'/config.php');

if ($cfg['output_file'])
{
	$lfile = $cfg['output_filepath'];

	if (file_exists($lfile))
	{
		$data = file_get_contents($lfile);

		if ($data === '') echo '[[ EMPTY ]]';
		else echo $data;
	}
	else
	{
		echo '[[ FILE NOT FOUND ]]';
	}
}
else if ($cfg['output_session'])
{
	if (session_status() !== PHP_SESSION_ACTIVE) session_start();

	$svar = $cfg['session_var'];

	if (isset($_GET['clear'])) if (key_exists($svar, $_SESSION)) $_SESSION[$svar] = '';

	if (key_exists($svar, $_SESSION))
	{
		if ($_SESSION[$svar] === '') echo '[[ NO OUTPUT ]]';
		else echo $_SESSION[$svar];
	}
	else
	{
		echo '[[ NO SESSION STARTED ]]';
	}
}
else
{
	echo '[[ NO USEFUL LOGGER CONFIGURED ]]';
}
