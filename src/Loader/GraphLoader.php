<?php

namespace Networq\Loader;

use Networq\Model\Graph;
use RuntimeException;
use Connector\Connector;

class GraphLoader
{
    protected $loader;

    public function __construct()
    {
        $this->loader = new PackageLoader();
    }

    public function load($filename)
    {
        $name = dirname($filename);
        $graph = new Graph($name);
        $pdo = null;
        if (getenv('NETWORQ_PDO')) {
            $connector = new Connector();
            $config = $connector->getConfig(getenv('NETWORQ_PDO'));
            $pdo = $connector->getPdo($config);
            $graph->setPdo($pdo);
        }

        // 1. recursively load all package definitions
        $rootPackage = $this->loadPackage($graph, $filename);
        $graph->setRootPackage($rootPackage);

        // 2. load types from packages
        foreach ($graph->getPackages() as $package) {
            $this->loader->loadTypes($package);
        }

        // 3a. load nodes from packages
        foreach ($graph->getPackages() as $package) {
            // Load standard /nodes
            $this->loader->loadNodes($package);

        }

        // 3b. load nodes from database
        if ($pdo) {
            $this->loader->loadDatabaseNodes($rootPackage, $pdo);
        }

        // 3c. Optionally load example nodes from rootPackage
        if (getenv('NETWORQ_EXAMPLES')) {
            $this->loader->loadNodes($rootPackage, '/examples');
        }

        // 4. load widgets from packages
        foreach ($graph->getPackages() as $package) {
            $this->loader->loadWidgets($package);
        }

        // 5. load queries from packages
        foreach ($graph->getPackages() as $package) {
            $this->loader->loadQueries($package);
        }
        return $graph;
    }

    public function loadPackage($graph, $filename)
    {
        $package = $this->loader->load($graph, $filename);
        $graph->addPackage($package);
        $envPath = rtrim(getenv('NETWORQ_PATH'), '/');
        if (!$envPath) {
            $envPath = '~/.nqp';
        }
        foreach ($package->getDependencies() as $dep) {
            if (!$graph->hasPackage($dep->getName())) {
                $filenames = [];
                $filenames[] = dirname($filename) . '/packages/' . str_replace(':', '/', $dep->getName()) . '-package/package.yaml';
                $filenames[] = dirname($filename) . '/packages/' . str_replace(':', '/', $dep->getName()) . '/package.yaml';
                if ($envPath) {
                    $filenames[] =  $envPath . '/' . str_replace(':', '/', $dep->getName()) . '-package/package.yaml';
                    $filenames[] =  $envPath . '/' . str_replace(':', '/', $dep->getName()) . '/package.yaml';
                }
                $depFilename = null;
                foreach ($filenames as $f) {
                    if (file_exists($f)) {
                        if (!$depFilename) {
                            $depFilename = $f;
                        }
                    }
                }
                if (!$depFilename) {
                    print_r($filenames);
                    throw new RuntimeException("Can't locate dependency package: " . $dep->getName());
                }
                $this->loadPackage($graph, $depFilename);
            }
        }
        return $package;
    }
}
