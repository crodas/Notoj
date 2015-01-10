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
namespace Notoj\Annotation;

use ArrayObject;

abstract class Base extends ArrayObject
{
    /** @Test */
    protected $annotations = array();
    protected $annotationsByName = array();
    protected $hasCache = array();

    public static function __set_state(Array $args)
    {
        $self = new self;
        foreach ($args as $key => $value) {
            $self->$key = $value;
        }
        return $self;
    }
    
    public function toCache()
    {
        return array('data' => $this->annotations, 'meta' => $this->meta);
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    protected function add(\Notoj\Annotation $value)
    {
        $this->annotationsByName[$value->getName()][] = $value;
        $this->annotations[] = $value;
    }

    public function has($index)
    {
        $index = strtolower($index);
        if ($this->checkManyCalls($index, 'has', $return, true))  {
            return !empty($return);
        }
        if (!array_key_exists($index, $this->hasCache)) {
            $this->hasCache[$index] = array_key_exists($index, $this->annotations);
        }

        return $this->hasCache[$index];
    }

    protected function checkManyCalls($index,  $method, &$return, $single = false)
    {
        if (strpos($index, ',')  === false) {
            return false;
        }
        $return = array();
        foreach (array_filter(array_unique(explode(",", $index))) as $name) {
            if (!$this->has($name)) {
                continue;
            }

            $value = $this->$method($name);
            if ($single) {
                $return = $value;
                return true;
            }
            $return = array_merge($return, $value);
        }

        return true;
    }

    public function getOne($index)
    {
        $index = strtolower($index);
        if ($this->checkManyCalls($index, 'getOne', $return, true))  {
            return $return;
        }

        if (!$this->has($index)) {
            return NULL;
        }

        return current($this->annotations[$index]);
    }

    public function get($index)
    {
        $index = strtolower($index);
        if ($this->checkManyCalls($index, 'get', $return))  {
            return $return;
        }
        if (!$this->has($index)) {
            return array();
        }

        return $this->annotations[$index];
    }
}
