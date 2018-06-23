<?php

namespace Networq\Loader;

use Symfony\Component\Yaml\Yaml;
use Networq\Model\Graph;
use Networq\Model\Package;
use Networq\Model\Field;
use Networq\Model\Property;
use Networq\Model\Dependency;
use Networq\Model\Tag;
use Networq\Model\Node;
use Networq\Model\Type;
use Networq\Model\Widget;
use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PackageLoader
{
    public function load(Graph $graph, $filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Package file not found");
        }
        $path = dirname($filename);

        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        $name = $data['name'] ?? null;
        if (!$name) {
            throw new RuntimeException("Package name not defined");
        }

        $package = new Package($graph, $name, $path);

        if (isset($data['dependencies'])) {
            foreach ($data['dependencies'] as $name => $details) {
                $version = $details;
                $dependency = new Dependency($name, $version);
                $package->addDependency($dependency);
            }
        }

        if (isset($data['navNodes'])) {
            $package->setNavNodeNames($data['navNodes']);
        }
        return $package;
    }

    private function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function importNode($package, $name, $data)
    {
        $node = new Node($package, $name);

        foreach ($data as $typeName => $fields) {
            if (!$package->getGraph()->hasType($typeName)) {
                throw new RuntimeException("Unknown type " . $typeName . ' for node ' . $node->getFqnn());
            }
            $type = $package->getGraph()->getType($typeName);
            $tag = new Tag($node, $type);
            if (is_array($fields)) {
                foreach ($fields as $k=>$v) {
                    if (!$type->hasField($k)) {
                        throw new RuntimeException("Unknown field: " . $type->getFqtn() . ' ' . $k . ' for node ' . $node->getFqnn());
                    }
                    $field = $type->getField($k);
                    if (is_array($v)) {
                        if ($this->isAssoc($v)) {

                            $newNodes = [];
                            foreach ($v as $n=>$d) {
                                $this->importNode($package, $n, $d);
                                $newNodes[] = $package->getName() . ':' . $n;
                            }
                            $v = $newNodes;
                        }
                    }

                    $p = $tag->getProperty($field->getName());
                    $p->setValue($v);
                }
            }
            $node->addTag($tag);
        }


        $package->addNode($node);
        return $node;
    }

    public function loadNodes(Package $package, $dir = '/nodes')
    {
        $path = $package->getPath() . $dir;
        if (!file_exists($path)) {
            return;
        }
        $it = new RecursiveDirectoryIterator($path);
        foreach (new RecursiveIteratorIterator($it) as $filename) {
            if (substr($filename, -5, 5)=='.yaml') {
                $name = substr(basename($filename), 0, -5);
                $yaml = file_get_contents($filename);
                $data = Yaml::parse($yaml);

                $node = $this->importNode($package, $name, $data);
            }
        }
    }

    public function loadTypes(Package $package)
    {
        $path = $package->getPath();
        if (!file_exists($path . '/types')) {
            return;
        }
        $it = new RecursiveDirectoryIterator($path . '/types');
        foreach (new RecursiveIteratorIterator($it) as $filename) {
            if (substr($filename, -5, 5)=='.yaml') {
                $name = substr(basename($filename), 0, -5);
                $yaml = file_get_contents($filename);
                $data = Yaml::parse($yaml);

                $type = new Type($package, $name);
                foreach ($data['fields'] as $fieldName=>$fieldData) {
                    $fieldType = $fieldData['type'];

                    $field = new Field($type, $fieldName, $fieldType);
                    $field->setReverse($fieldData['reverse'] ?? null);
                    $type->addField($field);
                }
                $package->addType($type);
            }
        }
    }

    public function loadWidgets(Package $package)
    {
        $filename = $package->getPath() . '/package.yaml';
        if (!file_exists($filename)) {
            return;
        }

        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        if (!isset($data['widgets'])) {
            return;
        }

        foreach ($data['widgets'] as $name => $w) {
            $typeName = $w['type'];
            $hookName = $w['hook'];

            $widget = new Widget($package, $name, $typeName, $hookName);
            $widget->setLabel($w['label'] ?? 'UNDEFINED');
            $package->addWidget($widget);
        }
    }
}
