<?php

namespace Notoj\Object;

abstract class Base
{
    protected $annotations;
    protected $object;

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
