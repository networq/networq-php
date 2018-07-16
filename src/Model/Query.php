<?php

namespace Networq\Model;

class Query
{
    protected $name;
    protected $package;
    protected $statement;

    public function __construct(Package $package, $name, array $statement)
    {
        $this->package = $package;
        $this->name = $name;
        $this->statement = $statement;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function getStatement()
    {
        return $this->statement;
    }

    public function getFqqn()
    {
        return $this->package->getFqpn() . ':' . $this->getName();
    }
}
