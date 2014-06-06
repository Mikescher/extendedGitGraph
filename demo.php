<?php

include 'extendedGitGraph.php';

$v = new ExtendedGitGraph('Mikescher');

//$v->authenticate('7e26c5f1621349c14a7d');

$v->setToken('7b3f6443cdd4b2f92d75c4c8aa83cfda6c7ca3ce');

$v->generate();