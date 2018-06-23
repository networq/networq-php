<?php

namespace Networq\Model;

use ArrayAccess;
use RuntimeException;

class Dependency
{
    protected $name;
    protected $version;

    public function __construct($name, $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }
}
