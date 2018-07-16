<?php

namespace Networq\Model;

use RuntimeException;

class Package
{
    protected $graph;
    protected $name;
    protected $ownerName;
    protected $description;
    protected $path;

    protected $nodes = [];
    protected $types = [];
    protected $queries = [];
    protected $widgets = [];
    protected $dependencies = [];
    protected $navNodeNames = [];
    protected $issues = [];

    public function __construct(Graph $graph, array $data, string $path)
    {
        $this->graph = $graph;
        $this->path = $path;

        $fqpn = $data['name'];
        if (!$fqpn) {
            throw new RuntimeException("Package name not defined: " . $path);
        }
        $part = explode(':', $fqpn);
        if (count($part)!=2) {
            throw new RuntimeException("Invalid Fully Qualified Package Name: " . $fqpn);
        }
        $this->ownerName = $part[0];
        $this->name = $part[1];

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

    public function getFqpn()
    {
        return $this->ownerName . ':' . $this->name;
    }

    public function getFqpnDir()
    {
        return $this->ownerName . '/' . $this->name;
    }

    public function getOwnerName()
    {
        return $this->ownerName;
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

    public function hasNode($name)
    {
        return isset($this->nodes[$name]);
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
            throw new RuntimeException("Unknown type: " . $this->getFqpn() . ':' . $name);
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


    public function getQueries()
    {
        return $this->queries;
    }

    public function addQuery(Query $query)
    {
        $this->queries[$query->getName()] = $query;
    }

    public function getQuery($name)
    {
        return $this->queries[$name];
    }

    public function addIssue(Issue $issue)
    {
        $this->issues[] = $issue;
    }

    public function getIssues()
    {
        return $this->issues;
    }

}
