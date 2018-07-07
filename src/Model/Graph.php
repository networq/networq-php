<?php

namespace Networq\Model;

use RuntimeException;
use Twig_Environment;
use Twig_SimpleFilter;
use PDO;

class Graph
{
    protected $packages = [];
    protected $rootPackage;
    protected $pdo;

    public function __construct()
    {
    }

    public function getRootPackage()
    {
        return $this->rootPackage;
    }


    public function setRootPackage(Package $package)
    {
        $this->rootPackage = $package;
    }

    public function addPackage(Package $package)
    {
        $this->packages[$package->getFqpn()] = $package;
    }

    public function hasNode($fqnn)
    {
        $fqnn = Fqnn::byFqnn($fqnn);
        $fqpn = $fqnn->getFqpn();
        $package = $this->packages[$fqpn];

        return $package->hasNode($fqnn->getName());
    }

    public function getNodes()
    {
        $res = [];
        foreach ($this->packages as $package) {
            $res = array_merge($res, $package->getNodes());
        }
        return $res;
    }


    public function getNode(string $fqnn)
    {
        // if (!$this->hasNode($id)) {
        // }
        $fqnn = Fqnn::byFqnn($fqnn);
        $fqpn = $fqnn->getFqpn();
        $package = $this->packages[$fqpn];

        return $package->getNode($fqnn->getName());
    }

    public function getTypes()
    {
        $res = [];
        foreach ($this->packages as $package) {
            foreach ($package->getTypes() as $type) {
                $res[$type->getFqtn()] = $type;
            }
        }
        return $res;
    }


    public function getNodeWidgets(Node $node, string $hookName)
    {
        $res = [];
        foreach ($this->packages as $package) {
            foreach ($package->getWidgets() as $widget) {
                $ok = true;
                if (!$node->hasTag($widget->getTypeName())) {
                    $ok = false;
                }
                if ($widget->getHookName() != $hookName) {
                    $ok = false;
                }
                if ($ok) {
                    $res[$widget->getName()] = $widget;
                }
            }
        }
        return $res;
    }

    public function hasType(string $fqtn)
    {
        try {
            $fqtn = Fqnn::byFqnn($fqtn);
        } catch (\Exception $e) {
            return false; // probably invalid fq format
        }
        $fqpn = $fqtn->getFqpn();
        if (!$this->hasPackage($fqpn)) {
            return false;
        }
        $package = $this->packages[$fqpn];

        return $package->hasType($fqtn->getName());
    }

    public function getType(string $fqtn)
    {
        if (!$this->hasType($fqtn)) {
            throw new RuntimeException("Undefined type: " . $fqtn);
        }
        $fqtn = Fqnn::byFqnn($fqtn);
        $fqpn = $fqtn->getFqpn();
        $package = $this->packages[$fqpn];

        return $package->getType($fqtn->getName());
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function hasPackage($name)
    {
        return isset($this->packages[$name]);
    }

    public function getPackage($name)
    {
        if (!$this->hasPackage($name)) {
            throw new RuntimeException("Unknown package: " . $name);
        }
        return $this->packages[$name];
    }

    public function registerTwig(Twig_Environment $twig)
    {
        $filter = new Twig_SimpleFilter('nodePath', function (Node $node) {
            return '/nodes/' . $node->getFqnn();
        });
        $twig->addFilter($filter);

        $filter = new Twig_SimpleFilter('nodeButton', function (Node $node) {
            $path = '/nodes/' . $node->getFqnn();
            $o = '<a href="' . $path . '" class="badge badge-success">';
            $o .= '<i class="fa fa-circle"></i> ';
            $o .= $node->getFqnn() . "</a>";
            return $o;
        });
        $twig->addFilter($filter);

        $filter = new Twig_SimpleFilter('typeButton', function (Type $type) {
            $path = '/types/' . $type->getFqtn();
            $o = '<a href="' . $path . '" class="badge badge-info">';
            $o .= '<i class="fa fa-cube"></i> ';
            $o .= $type->getFqtn() . "</a>";
            return $o;
        });
        $twig->addFilter($filter);

        $filter = new Twig_SimpleFilter('packageButton', function (Package $package) {
            $path = '/packages/' . $package->getFqpn();
            $o = '<a href="' . $path . '" class="badge badge-secondary">';
            $o .= '<i class="fa fa-book"></i> ';
            $o .= $package->getFqpn() . "</a>";
            return $o;
        });
        $twig->addFilter($filter);

        // register template namespaces
        foreach ($this->packages as $package) {
            $path = $package->getPath() . '/templates';
            if (file_exists($path)) {
                $twig->getLoader()->addPath($path, $package->getFqpn());
            }
        }
    }

    public function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getNodeYaml(string $fqnn)
    {
        $stmt = $this->pdo->prepare(
            'SELECT data
            FROM node WHERE
            fqnn=:fqnn
            '
        );
        $stmt->execute(
            [
                'fqnn' => $fqnn
            ]
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return $row['data'];
    }

    public function persist(string $fqnn, string $yaml)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO node
            (fqnn, data) VALUES(:fqnn, :data)
            ON DUPLICATE KEY UPDATE
            data=:data
            '
        );
        $stmt->execute(
            [
                'fqnn' => $fqnn,
                'data' => $yaml
            ]
        );
    }

}
