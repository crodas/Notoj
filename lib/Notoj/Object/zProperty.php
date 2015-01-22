<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TProperty;
use Notoj\Annotations;

class zProperty extends Base
{
    public function __construct(TProperty $property, Annotations $annotations)
    {
        $this->object      = $property;
        $this->annotations = $annotations;
        $annotations->setObject($this);
    }
}


