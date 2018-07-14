<?php

use PHPUnit\Framework\TestCase;
use Networq\Loader\GraphLoader;

require_once __DIR__ . '/../vendor/autoload.php';

class NetworqPHPTestCase extends TestCase
{
    protected function getGraph()
    {
        $filename = __DIR__ . '/../example/package/package.yaml';
        $loader = new GraphLoader();
        return $loader->load($filename);
    }
}
