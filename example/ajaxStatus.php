<?php

$cfg = require (__DIR__.'/config.php');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (isset($_GET['clear']))
{
	if (key_exists($cfg['session_var'], $_SESSION)) $_SESSION[$cfg['session_var']] = '';
}

if (key_exists($cfg['session_var'], $_SESSION))
	echo $_SESSION[$cfg['session_var']];
else
	echo '[[ NO SESSION STARTED ]]';
