<?php

namespace Notoj\Object;

abstract class zClassMember extends Base
{
    public function getClass()
    {
        return new zClass($this->object->class, NULL);
    }
}
