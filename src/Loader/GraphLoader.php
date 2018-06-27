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

    public function loadPackage($graph, $filename)
    {
        $package = $this->loader->load($graph, $filename);
        $graph->addPackage($package);
        $envPath = rtrim(getenv('NETWORQ_PATH'), '/');
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
