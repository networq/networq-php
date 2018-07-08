<?php

namespace Networq\Model;

class Issue
{
    protected $code;
    protected $message;
    protected $node;

    public function __construct($code, $message, $fqnn = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->fqnn = $fqnn;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getFqnn()
    {
        return $this->fqnn;
    }
}
