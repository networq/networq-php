<?php

namespace Networq\Model;

use Symfony\Component\Yaml\Yaml;
use ArrayAccess;
use RuntimeException;

class Node implements ArrayAccess
{
    protected $name;
    protected $package;
    protected $tags = [];
    protected $editable;

    public function __construct(Package $package, $name, $editable = false)
    {
        $this->package = $package;
        $this->name = $name;
        $this->editable = $editable;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isEditable()
    {
        return $this->editable;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function getFqnn()
    {
        return $this->package->getFqpn() . ':' . $this->name;
    }

    public function __toString()
    {
        return '*' . $this->getFqnn();
    }

    public function addTag(Tag $tag)
    {
        $this->tags[$tag->getType()->getFqtn()] = $tag;
    }

    public function hasTag($name)
    {
        return isset($this->tags[$name]);
    }

    public function getTag($name)
    {
        return $this->tags[$name];
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getProperty($tagName, $fieldName)
    {
        $tag = $this->getTag($tagName);
        return $tag->getProperty($fieldName);
    }

    public function getPropertyValue($tagName, $fieldName)
    {
        $property = $this->getProperty($tagName, $fieldName);
        return $property->getValue();
    }


    public function offsetSet($offset, $value) {
        throw new RuntimeException("Node is immutable");
    }

    public function offsetUnset($offset) {
        throw new RuntimeException("Node is immutable");
    }

    public function offsetExists($offset) {
        return $this->hasTag($offset);
    }

    public function offsetGet($offset) {
        return $this->getTag($offset);
    }

    public function toData()
    {
        $data = [];
        foreach ($this->tags as $tag) {
            $tagData = [];
            foreach ($tag->getProperties() as $property) {
                if (!$property->getField()->getReverse()) {
                    $tagData[$property->getField()->getName()] = $property->getValueRaw();
                }
            }
            $data[$tag->getType()->getFqtn()] = $tagData;
        }
        return $data;
    }

    public function toYaml()
    {
        $yaml = Yaml::dump($this->toData(), 10, 4);
        $yaml = str_replace(': {  }', ': ~', $yaml);
        return $yaml;
    }

}
