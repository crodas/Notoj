<?php

namespace Notoj;

class Annotation
{
    protected $name;
    protected $args;
    protected $object; 

    public function __construct($name, Array $args = array())
    {
        $this->name = strtolower($name);
        $this->args = $args;
    }

    public function toCache()
    {
        return array($this->name, $this->args);
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject(Object\Base $object)
    {
        $this->object = $object;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getName()
    {
        return $this->name;
    }
}
