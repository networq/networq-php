<?php

namespace Networq\Model;

use ArrayAccess;
use RuntimeException;

class Node implements ArrayAccess
{
    protected $name;
    protected $package;
    protected $tags = [];

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

    public function getFqnn()
    {
        return $this->package->getName() . ':' . $this->name;
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

}
