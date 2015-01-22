<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2012 César Rodas                                                  |
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

namespace Notoj;

use RuntimeException;
use InvalidArgumentException;
use ArrayAccess;
use Iterator;

class Annotations implements ArrayAccess, Iterator
{
    protected $object;
    protected $aIndex = array();
    protected $annotations;
    protected $merges = array();
    protected $index = 0;

    public function isClass()
    {
        return $this->object instanceof Object\zClass;
    }

    public function isFunction()
    {
        return $this->object instanceof Object\zFunction;
    }

    public function isProperty()
    {
        return $this->object instanceof Object\zProperty;
    }

    public function isMethod()
    {
        return $this->object instanceof Object\zMethod;
    }

    public function valid()
    {
        return array_key_exists($this->index, $this->annotations);
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        ++$this->index;
    }

    public function getFile()
    {
        return $this->object->getFile();
    }

    public function current()
    {
        if (!empty($this->object)) {
            return $this->annotations[$this->index]->setObject($this->object);
        }
        return $this->annotations[$this->index];
    }

    public function setObject(Object\Base $obj)
    {
        $this->object = $obj;
        return $this;
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->annotations);
    }

    public function get($selector)
    {
        $aReturn = array();
        foreach (explode(",", strtolower($selector)) as $sel) {
            if (!empty($this->aIndex[$sel])) {
                $aReturn = array_merge($aReturn, $this->aIndex[$sel]);
            }
        }

        return $aReturn;
    }

    public function count()
    {
        return count($this->annotations);
    }

    public function offsetGet($index)
    {
        return $this->annotations[$index];
    }

    public function offsetSet($index, $value)
    {
        throw new \RuntimeException("You are not allowed");
    }
    
    public function offsetUnset ($offset)
    {
        throw new \RuntimeException("You are not allowed");
    }

    protected function buildIndex()
    {
        if (empty($this->aIndex)) {
            foreach ($this->annotations as $ann) {
                $this->aIndex[$ann->getName()][] = $ann;
            }
        }

        $merge = end($this->merges);
        if ($merge) {
            $merge->buildIndex();
            $this->aIndex = array_merge($this->aIndex, $merge->aIndex);
        }
    }

    public function fromCache(Array $cache)
    {
        $annotations = new self;
        foreach ($cache as $object => $ann) {
            $object = unserialize($object);
            $ann    = array_map(function($a) {
                return Annotation::fromCache($a);
            }, $ann);
            $obj = new self($ann);
            if ($object) {
                $obj->setObject($object);
            }
            $annotations->merge($obj);
        }

        return $annotations;
    }

    public function toCache()
    {
        $objects = array();
        foreach ($this->annotations as $ann) {
            $objects[] = $ann->toCache();
        }
        return $objects;
    }

    public function __construct(Array $annotations = array())
    {
        $this->annotations = $annotations;
        foreach ($this->annotations as $annotation) {
            $annotation->setParent($this);
        }
        $this->buildIndex();
    }
}
