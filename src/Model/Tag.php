<?php

namespace Networq\Model;

use ArrayAccess;
use RuntimeException;

class Tag implements ArrayAccess
{
    protected $typeName;
    protected $name;
    protected $package;
    protected $properties = [];

    public function __construct(Node $node, Type $type)
    {
        $this->node = $node;
        $this->type = $type;
        foreach ($type->getFields() as $field) {
            $p = new Property($this, $field, null);
            $this->properties[$field->getName()] = $p;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getProperty($name)
    {
        if (!$this->hasProperty($name)) {
            throw new RuntimeException("Tag " . $this->getType()->getFqtn() . " on " . $this->getNode()->getFqnn() . " does not have property: " . $name);
        }
        return $this->properties[$name];
    }

    public function getPropertyValue($name)
    {
        if (!$this->hasProperty($name)) {
            throw new RuntimeException('No such property: ' . $name);
        }
        $property = $this->getProperty($name);
        return $property->getValue();
    }

    public function offsetSet($offset, $value) {
        throw new RuntimeException("Tag is immutable");
    }

    public function offsetUnset($offset) {
        throw new RuntimeException("Tag is immutable");
    }

    public function offsetExists($offset) {
        return $this->hasProperty($offset);
    }

    public function offsetGet($offset) {
        return $this->getPropertyValue($offset);
    }
}
