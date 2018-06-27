<?php

namespace Networq\Model;

use RuntimeException;

class Package
{
    protected $graph;
    protected $name;
    protected $description;
    protected $path;

    protected $nodes = [];
    protected $types = [];
    protected $widgets = [];
    protected $dependencies = [];
    protected $navNodeNames = [];

    public function __construct(Graph $graph, array $data, string $path)
    {
        $this->graph = $graph;
        $this->path = $path;
        $this->name = $data['name'];
        if (!$this->name) {
            throw new RuntimeException("Package name not defined: " . $path);
        }

        $this->description = $data['description'] ?? null;
        $this->license = $data['license'] ?? null;
    }

    public function setNavNodeNames($nodeNames)
    {
        $this->navNodeNames = $nodeNames;
    }

    public function getNavNodes()
    {
        $nodes = [];
        foreach ($this->navNodeNames as $fqnn) {
            $nodes[] = $this->graph->getNode($fqnn);
        }
        return $nodes;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function getGraph()
    {
        return $this->graph;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function addNode(Node $node)
    {
        $this->nodes[$node->getName()] = $node;
    }

    public function getNode($name)
    {
        return $this->nodes[$name];
    }

    public function addType(Type $type)
    {
        $this->types[$type->getName()] = $type;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new RuntimeException("Unknown type: " . $this->name . ':' . $name);
        }
        return $this->types[$name];
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function addDependency(Dependency $dependency)
    {
        $this->dependencies[$dependency->getName()] = $dependency;
    }

    public function getWidgets()
    {
        return $this->widgets;
    }

    public function addWidget(Widget $widget)
    {
        $this->widgets[$widget->getName()] = $widget;
    }

    public function getWidget($name)
    {
        return $this->widgets[$name];
    }

}
