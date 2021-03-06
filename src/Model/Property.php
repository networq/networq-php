<?php

namespace Networq\Model;

use RuntimeException;

class Property
{
    protected $tag;
    protected $field;
    protected $value;

    public function __construct(Tag $tag, Field $field, $value)
    {
        $this->tag = $tag;
        $this->field = $field;
        $this->value = $value;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setValue($v)
    {
        $this->value = $v;
    }

    public function getValueRaw()
    {
        return $this->value;
    }

    public function getValueString()
    {
        if (is_array($this->value)) {
            $o = '';
            foreach ($this->value as $v) {
                $o .= $v . ' ';
            }
            return trim($o);
        } else {
            return $this->value;
        }
    }

    public function getValue()
    {
        $value = $this->value;

        if ($this->field->getReverse()) {
            $ft = $this->field->getType();
            $ft = str_replace('[]', '', $ft);
            $graph = $this->field->getNodeType()->getPackage()->getGraph();
            if (!$graph->hasType($ft)) {
                throw new RuntimeException("Can't reverse property of type " . $ft . ' on field ' . $this->field->getFqfn());
            }
            $ct = $graph->getType($ft);
            $nodes = $ct->getNodesWhere($ft, $this->field->getReverse(), $this->tag->getNode()->getFqnn());
            return $nodes;
        }
        if (!$value) {
            return $value;
        }
        if ($this->field->getType()!='string') {
            $graph = $this->field->getNodeType()->getPackage()->getGraph();
            if (!is_array($this->value)) {
                if (!$graph->hasNode($this->value)) {
                    return null;
                }
                $value = $graph->getNode($this->value);
            } else {
                $res = [];
                foreach ($this->value as $v) {
                    $res[] = $graph->getNode($v);
                }
                $value = $res;
            }
        }
        return $value;
    }

}
