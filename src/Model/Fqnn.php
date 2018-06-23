<?php
namespace Networq\Model;

use RuntimeException;

class Fqnn
{
    protected $owner;
    protected $package;
    protected $name;

    private function __construct()
    {

    }
    public static function byFqnn(string $fqnn)
    {
        $part = explode(':', $fqnn);
        if (count($part)!=3) {
            throw new RuntimeException("Invalid FQNN: " . $fqnn);
        }
        $fqnn = new Fqnn();
        $fqnn->owner = $part[0];
        $fqnn->package = $part[1];
        $fqnn->name = $part[2];
        return $fqnn;
    }

    public function getOwner()
    {
        return $this->owner;
    }
    public function getPackage()
    {
        return $this->package;
    }
    public function getName()
    {
        return $this->name;
    }

    public function getFqpn()
    {
        return $this->owner . ':' . $this->package;
    }
}
