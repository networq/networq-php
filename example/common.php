<?php

use Networq\Loader\GraphLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$filename = __DIR__ . '/package/package.yaml';
$loader = new GraphLoader();
$graph = $loader->load($filename);
