<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TFunction;
use Notoj\Annotations;

class zFunction extends Base
{
    protected $object;

    public function __construct(TFunction $obj, Annotations $annotations)
    {
        $this->object      = $obj;
        $this->annotations = $annotations;
        $annotations->setObject($this);
    }
}

