<?php

namespace Networq\Model;

use ArrayAccess;
use RuntimeException;

class Widget  // implements ArrayAccess
{
    protected $name;
    protected $typeName;
    protected $hookName;
    protected $label;

    public function __construct(Package $package, $name, $typeName, $hookName)
    {
        $this->package = $package;
        $this->name = $name;
        $this->typeName = $typeName;
        $this->hookName = $hookName;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTypeName()
    {
        return $this->typeName;
    }

    public function getHookName()
    {
        return $this->hookName;
    }

    public function getTemplateName()
    {
        return '@' . $this->package->getName() . '/' . $this->name . '.html.twig';
    }

}
