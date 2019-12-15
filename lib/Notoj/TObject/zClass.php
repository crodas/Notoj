<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2014 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/
namespace Notoj\TObject;

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

    public function getTraits($filter = '')
    {
        return $this->getType($filter, 'getTraits', __NAMESPACE__ . '\zClass');
    }

    public function getProperties($filter = '')
    {
        return $this->getType($filter, 'getProperties', __NAMESPACE__ . '\zProperty');
    }

    public function getMethods($filter = '')
    {
        return $this->getType($filter, 'getMethods', __NAMESPACE__ . '\zMethod');
    }

    public function isFinal()
    {
        $mods = $this->object->getMods();
        return in_array('final', $mods);
    }

    public function isAbstract()
    {
        $mods = $this->object->getMods();
        return in_array('abstract', $mods);
    }

    public function isClass()
    {
        return true;
    }
}