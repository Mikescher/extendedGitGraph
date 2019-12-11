<?php

require_once '../egg/ExtendedGitGraph2.php';

$cfg = require 'config.php';

$egg = new ExtendedGitGraph2($cfg);

$egg->update();