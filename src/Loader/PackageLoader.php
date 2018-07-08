<?php

namespace Networq\Loader;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Networq\Model\Graph;
use Networq\Model\Package;
use Networq\Model\Field;
use Networq\Model\Property;
use Networq\Model\Dependency;
use Networq\Model\Tag;
use Networq\Model\Node;
use Networq\Model\Type;
use Networq\Model\Issue;
use Networq\Model\Widget;
use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PDO;

class PackageLoader
{
    public function load(Graph $graph, $filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Package file not found: " . $filename);
        }
        $path = dirname($filename);

        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        $name = $data['name'] ?? null;

        $package = new Package($graph, $data, $path);

        if (isset($data['dependencies'])) {
            foreach ($data['dependencies'] as $name => $details) {
                $version = $details;
                if ($version != 'latest') {
                    throw new RuntimeException("Package " . $package->getFqpn() . ' requests version `' . $version . '` of ' . $name . '. Only `latest` is currently supported.');
                }
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

    public function importNode($package, $name, array $data, bool $editable = false, Node $parent = null)
    {
        $node = new Node($package, $name, $editable);
        foreach ($data as $typeName => $fields) {
            if (!$package->getGraph()->hasType($typeName)) {
                $issue = new Issue('REFERENCED_UNDEFINED_TYPE', 'Undefined type referenced: ' . $typeName, $node->getFqnn());
                $package->addIssue($issue);
            } else {
                $type = $package->getGraph()->getType($typeName);
                $tag = new Tag($node, $type);
                if (is_array($fields)) {
                    foreach ($fields as $k=>$v) {
                        if ($v=='$') {
                            if ($parent) {
                                $v = $parent->getFqnn();
                            }
                        }
                        if (!$type->hasField($k)) {
                            $issue = new Issue('UNKNOWN_TYPE_FIELD', 'Unknown type-field: ' . $type->getFqtn() . '.' . $k, $node->getFqnn());
                            $package->addIssue($issue);
                        } else {
                            $field = $type->getField($k);
                            if (is_array($v)) {
                                if ($this->isAssoc($v)) {

                                    $newNodes = [];
                                    foreach ($v as $n=>$d) {
                                        if ($n[0]=='~') {
                                            $n = $name . $n;
                                        }
                                        $this->importNode($package, $n, $d, $editable, $node);
                                        $newNodes[] = $package->getFqpn() . ':' . $n;
                                    }
                                    $v = $newNodes;
                                }
                            }
                            $p = $tag->getProperty($field->getName());
                            $p->setValue($v);
                        }

                    }
                }
                $node->addTag($tag);
            }
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

    // public function loadDatabaseNodes(Package $package, PDO $pdo)
    // {
    //     $stmt = $pdo->prepare(
    //         'SELECT node.fqnn, tag.fqtn, property.field, property.value
    //         FROM node
    //         LEFT JOIN tag ON tag.fqnn=node.fqnn
    //         LEFT JOIN property ON
    //             (
    //                 property.fqnn=node.fqnn
    //                 AND property.fqtn=tag.fqtn
    //             )
    //         '
    //     );
    //     $stmt->execute();
    //     $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     $lastFqnn = null;
    //     $data = [];
    //     foreach ($rows as $row) {
    //         $fqnn = $row['fqnn'];
    //         $fqtn = $row['fqtn'];
    //         $field = $row['field'];
    //         $value = $row['value'];
    //         if (!isset($datas[$fqnn])) {
    //             $datas[$fqnn] = [];
    //         }
    //         if ($fqtn) {
    //             if (!isset($datas[$fqnn][$fqtn])) {
    //                 $datas[$fqnn][$fqtn] = [];
    //             }
    //             if ($field) {
    //                 $datas[$fqnn][$fqtn][$field] = $value;
    //             }
    //         }
    //     }
    //     // print_r($datas);
    //     foreach ($datas as $fqnn => $data) {
    //         $part = explode(':', $fqnn);
    //         $name = $part[2];
    //         $node = $this->importNode($package, $name, $data);
    //     }
    // }

    public function loadDatabaseNodes(Package $package, PDO $pdo)
    {
        $stmt = $pdo->prepare(
            'SELECT fqnn, data
            FROM node
            '
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $fqnn = $row['fqnn'];
            $yaml = $row['data'];
            $data = [];
            if ($yaml) {
                try {
                    $data = Yaml::parse($yaml);
                } catch (ParseException $e) {
                    $issue = new Issue('PARSE_ERROR', 'Error parsing YAML: ' . $e->getMessage(), $fqnn);
                    $package->addIssue($issue);
                }
                if (!is_array($data)) {
                    $issue = new Issue('DATA_IS_NOT_ARRAY', 'Data contains valid YAML, but is not an array', $fqnn);
                    $package->addIssue($issue);
                    $data = [];
                }
            }
            $part = explode(':', $fqnn);
            $name = $part[2];
            $node = $this->importNode($package, $name, $data, true);
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
                if (isset($data['fields']) && is_array($data['fields'])) {
                    foreach ($data['fields'] as $fieldName=>$fieldData) {
                        $fieldType = $fieldData['type'];

                        $field = new Field($type, $fieldName, $fieldType);
                        $field->setReverse($fieldData['reverse'] ?? null);
                        $type->addField($field);
                    }
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
