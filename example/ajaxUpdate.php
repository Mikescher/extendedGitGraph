<?php

require_once '../egg/ExtendedGitGraph2.php';

$cfg = require 'config.php';

$egg = new ExtendedGitGraph2($cfg);

$r = $egg->update();

if ($r) http_response_code(200); else http_response_code(500);