<?php

namespace Notoj\Annotation;

class Annotation extends Common
{
    protected $name;
    protected $args;
    protected $parent; 

    public function getObjectName()
    {
        return $this->parent->getObjectName();
    }


    public function __construct($name, Array $args = array())
    {
        $this->name = strtolower($name);
        $this->args = $args;
    }

    public static function fromCache(Array $object)
    {
        $obj = new self($object[0], $object[1]);
        $obj->object = unserialize($object[2]);
        return $obj;
    }

    public function toCache()
    {
        return array($this->name, $this->args);
    }

    public function setParent(Annotations $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getFile()
    {
        return $this->parent->getFile();
    }

    public function getObject()
    {
        return $this->parent->getObject();
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