<?php

namespace Networq\Model;

class Field
{
    protected $name;
    protected $reverse = null;

    public function __construct(Type $nodeType, $name, $type)
    {
        $this->nodeType = $nodeType;
        $this->name = $name;
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isNodeListType()
    {
        if (substr($this->type, -2, 2)!='[]') {
            return false;
        }
        if (count(explode(':', $this->type))==3) {
            return true;
        }
        return false;
    }

    public function isString()
    {
        if (substr($this->type, -2, 2)=='[]') {
            return false;
        }
        if (count(explode(':', $this->type))==3) {
            return false;
        }
        return true;
    }

    public function isNodeType()
    {
        if (substr($this->type, -2, 2)=='[]') {
            return false;
        }
        if (count(explode(':', $this->type))==3) {
            return true;
        }
        return false;
    }

    public function getNodeType()
    {
        return $this->nodeType;
    }

    public function getDefault()
    {
        if (substr($this->type, -2, 2)=='[]') {
            return array();
        }
        return null;
    }

    public function setReverse($reverse)
    {
        $this->reverse = $reverse;
    }

    public function getReverse()
    {
        return $this->reverse;
    }

}
