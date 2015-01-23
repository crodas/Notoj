<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TClass;
use Notoj\Annotation\Annotations;
use Notoj\Cacheable;

class zClass extends Base
{
    public function getParent(Cacheable $cache)
    {
        $parent = $this->object->getParent();
        if (empty($parent)) {
            return NULL;
        }

        return new self($parent, NULL);
    }
}
