<?php

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../config.php';

use HTML5test\Automate\Main;

$main = new Main($config);
$main->run();