<?php

namespace Notoj\Object;

abstract class Base implements \ArrayAccess
{
    protected $annotations;
    protected $object;

    public function offsetUnset($name)
    {
        throw new \BadFunctionCallException;
    }

    public function offsetSet($name, $value)
    {
        throw new \BadFunctionCallException;
    }

    public function offsetExists($name)
    {
        return $this->annotations->has($name);
    }

    public function offsetGet($name)
    {
        return $this->annotations->getOne($name);
    }

    public function getFile()
    {
        return $this->object->getFile();
    }

    public function get($selector)
    {
        return $this->annotations->get($selector);
    }


    public function getName()
    {
        return $this->object->getName();
    }

    public function has($selector)
    {
        return $this->annotations->has($selector);
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }
}
