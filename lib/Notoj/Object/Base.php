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
        $name = strtolower($name);
        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($name)
    {
        $name = strtolower($name);
        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() == $name) {
                return $annotation;
            }
        }

        return NULL;
    }

    public function getFile()
    {
        return $this->object->getFile();
    }

    public function has($selector)
    {
        foreach ($this->annotations as $ann) {
            if ($ann->has($selector)) {
                return true;
            }
        }

        return false;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }
}
