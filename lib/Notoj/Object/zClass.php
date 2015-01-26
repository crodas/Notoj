<?php

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TClass;
use Notoj\Annotation\Annotations;
use Notoj\Cacheable;

class zClass extends Base
{
    public function getParent()
    {
        $parent = $this->object->getParent();
        if (empty($parent)) {
            return NULL;
        }

        return new self($parent, NULL);
    }

    protected function getType($filter, $method, $class)
    {
        $members = array();
        foreach ($this->object->$method() as $member) {
            $member = new $class($member, NULL);
            if (!$filter || $member->has($filter)) {
                $members[] = $member;
            }
        }

        return $members;
    }

    public function getProperties($filter = '')
    {
        return $this->getType($filter, 'getProperties', __NAMESPACE__ . '\zProperty');
    }

    public function getMethods($filter = '')
    {
        return $this->getType($filter, 'getMethods', __NAMESPACE__ . '\zMethod');
    }
}
