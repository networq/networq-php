<?php

namespace Networq\Loader;

use Networq\Model\Graph;
use RuntimeException;

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

        // 1. recursively load all package definitions
        $rootPackage = $this->loadPackage($graph, $filename);
        $graph->setRootPackage($rootPackage);

        // 2. load types from packages
        foreach ($graph->getPackages() as $package) {
            $this->loader->loadTypes($package);
        }

        // 3. load nodes from packages
        foreach ($graph->getPackages() as $package) {
            // Load standard /nodes
            $this->loader->loadNodes($package);

        }

        // 3b. Optionally load example nodes from rootPackage
        if (getenv('NETWORQ_EXAMPLES')) {
            $this->loader->loadNodes($rootPackage, '/examples');
        }

        // 4. load widgets from packages
        foreach ($graph->getPackages() as $package) {
            $this->loader->loadWidgets($package);
        }
        return $graph;
    }

    protected function loadPackage($graph, $filename)
    {
        $package = $this->loader->load($graph, $filename);
        $graph->addPackage($package);
        foreach ($package->getDependencies() as $dep) {
            if (!$graph->hasPackage($dep->getName())) {
                $packageFilename = str_replace(':', '/', $dep->getName()) . '/package.yaml';

                // Check local project packages/ directory
                $depFilename = dirname($filename) . '/packages/' . $packageFilename;

                if (!file_exists($depFilename)) {
                    // Check global NETWORQ_PATH directory
                    $envPath = getenv('NETWORQ_PATH');
                    if ($envPath) {
                        $depFilename = $envPath . '/' . $packageFilename;
                    }
                }
                if (!file_exists($depFilename)) {
                    throw new RuntimeException("Missing dependency: " . $dep->getName());
                }
                $this->loadPackage($graph, $depFilename);
            }
        }
        return $package;
    }
}
