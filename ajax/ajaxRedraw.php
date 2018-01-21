<?php

include __DIR__ . '/../src/ExtendedGitGraph.php';

$v = require 'ajaxSecret.php';

$v->init();

$v->updateFromCache();

$v->setColorScheme($_GET['scheme']);
$v->generate();

foreach ($v->get() as $year => $html) {

	echo $html;
	echo "\n\n\n<br/>\n\n\n";

}