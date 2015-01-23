<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TClass;
use Notoj\Annotation\Annotations;
use Notoj\Cacheable;

class zClass extends Base
{
    public function __construct(TClass $class, Annotations $annotations)
    {
        $this->object      = $class;
        $this->annotations = $annotations;
        $annotations->setObject($this);
    }

    public function getParent(Cacheable $cache)
    {
        $parent = $this->object->getParent();
        if (empty($parent)) {
            return NULL;
        }

        return $cache->getClassByName($parent->getName());
    }
}
