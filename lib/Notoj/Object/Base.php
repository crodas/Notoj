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

namespace Notoj\Object;

use crodas\ClassInfo\Definition\TBase;
use Notoj\Notoj;

abstract class Base implements \ArrayAccess
{
    protected $annotations;
    protected $object;

    public function getObject()
    {
        return $this->object;
    }

    public function offsetUnset($name)
    {
        throw new \BadFunctionCallException;
    }

    public function offsetSet($name, $value)
    {
        throw new \BadFunctionCallException;
    }

    public function offsetExists($name)
    {
        return $this->annotations->has($name);
    }

    public function offsetGet($name)
    {
        return $this->annotations->getOne($name);
    }

    public function getFile()
    {
        return $this->object->getFile();
    }

    protected function __construct(TBase $object, $localCache)
    {
        $this->object = $object;
        $this->annotations = Notoj::parseDocComment($object->GetPHPDoc(), $foo, $localCache);
        $this->annotations->setObject($this);
    }

    public static function create(TBase $object, $localCache)
    {
        $type = substr(strstr(get_class($object), "\\T"), 2);
        if ($type == 'Function' && !empty($object->class)) {
            $type = 'Method';
        }
        $class = __NAMESPACE__ . "\\z{$type}";
        return new $class($object, $localCache);
    }

    public function get($selector = '')
    {
        return $this->annotations->get($selector);
    }


    public function getOne($selector = '')
    {
        return $this->annotations->getOne($selector);
    }

    public function getLine()
    {
        return $this->object->getStartLine();
    }

    public function getName()
    {
        return $this->object->getName();
    }

    public function has($selector)
    {
        return $this->annotations->has($selector);
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function isMethod()
    {
        return false;
    }

    public function isClass()
    {
        return false;
    }

    public function isProperty()
    {
        return false;
    }

    public function isFunction()
    {
        return false;
    }
}
