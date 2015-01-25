<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TBase;
use Notoj\Notoj;

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

    protected function __construct(TBase $object, $localCache)
    {
        $this->object = $object;
        $this->annotations = Notoj::parseDocComment($object->GetPHPDoc(), $foo, $localCache);
        $this->annotations->setObject($this);
    }

    public static function create(TBase $object, $localCache)
    {
        $type = substr(strstr(get_class($object), "\\T"), 2);
        if ($type == 'Function' && !empty($object->class)) {
            $type = 'Method';
        }
        $class = __NAMESPACE__ . "\\z{$type}";
        return new $class($object, $localCache);
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
