<?php

namespace Networq\Model;

class Type
{
    protected $name;
    protected $package;
    protected $fields = [];

    public function __construct(Package $package, $name)
    {
        $this->package = $package;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function getFqtn()
    {
        return $this->package->getFqpn() . ':' . $this->getName();
    }

    public function addField(Field $field)
    {
        $this->fields[$field->getName()] = $field;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    public function getField($name)
    {
        return $this->fields[$name];
    }

    public function getNodes()
    {
        $res = [];
        $graph = $this->package->getGraph();
        foreach ($graph->getNodes() as $node) {
            if ($node->hasTag($this->getFqtn())) {
                $res[] = $node;
            }
        }
        return $res;
    }

    public function getNodesWhere(string $tagName, string $fieldName, $value)
    {
        $nodes = $this->getNodes();

        $res = [];

        foreach ($nodes as $node) {
            if ($node->hasTag($tagName)) {
                $tag = $node->getTag($tagName);
                $p = $tag->getProperty($fieldName);
                $v = $p->getValueRaw();
                if (is_array($v)) {
                    foreach ($v as $v2) {
                        if ($v2 == $value) {
                            $res[] = $node;
                        }
                    }
                } else {
                    if ($v == $value) {
                        $res[] = $node;
                    }
                }
            }
        }
        return $res;
    }
}
