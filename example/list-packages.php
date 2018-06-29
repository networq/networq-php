<?php

require_once 'common.php';

$packages = $graph->getPackages();
echo "Packages: " . count($packages) . PHP_EOL;
foreach ($packages as $package) {
    echo " - " . $package->getFqpn() . PHP_EOL;
}
