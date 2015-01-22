<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TClass;
use Notoj\Annotations;

class zClass extends Base
{
    public function __construct(TClass $class, Annotations $annotations)
    {
        $this->object      = $class;
        $this->annotations = $annotations;
        $annotations->setObject($this);
    }
}
