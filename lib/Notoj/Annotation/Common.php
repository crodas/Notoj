<?php

namespace Notoj\Annotation;

use Notoj\Object\Base;

abstract class Common 
{
    protected $object;

    public function isClass()
    {
        return $this->object instanceof Object\zClass;
    }

    public function isFunction()
    {
        return $this->object instanceof Object\zFunction;
    }

    public function isProperty()
    {
        return $this->object instanceof Object\zProperty;
    }

    public function isMethod()
    {
        return $this->object instanceof Object\zMethod;
    }

}
