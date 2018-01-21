<?php

include __DIR__ . '/../src/ExtendedGitGraph.php';

$v = require 'ajaxSecret.php';

$v->init();

$v->updateFromCache();

$v->setColorScheme($_GET['scheme']);
$v->generate();

$a = '';
foreach ($v->get() as $year => $html) {

	echo $html;
	echo "\n\n\n";

	$a .= $html . "\n";
}

if (key_exists('doOutput', $_GET)) file_put_contents(__DIR__ . '/../output/out_all.html', $a);