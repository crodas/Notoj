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
    protected $keys   = array();
    protected $ikeys  = array();
    protected $values = array();
    
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

    protected function add($key, $value)
    {
        $index = count($this->values);
        $this->values[$index] = $value;

        if (empty($this->keys[$key])) {
            $this->keys[$key] = array();
        }
        $this->keys[$key][] = $index;

        $key = strtolower($key);
        if (empty($this->ikeys[$key])) {
            $this->ikeys[$key] = array();
        }
        $this->ikeys[$key][] = $index;
    }

    public function has($index, $caseSensitive = true)
    {
        if ($this->checkManyCalls($index, $caseSensitive, 'has', $return, true))  {
            return !empty($return);
        }
        $key = $index . ($caseSensitive ? "_0"  : "_1");
        if (!array_key_exists($key, $this->hasCache)) {
            $source = $caseSensitive ? $this->keys : $this->ikeys;
            if (!$caseSensitive) {
                $index = strtolower($index);
            }
            $this->hasCache[$key] = array_key_exists($index, $source);
        }

        return $this->hasCache[$key];
    }

    protected function checkManyCalls($index,  $cs, $method, &$return, $single = false)
    {
        if (strpos($index, ',')  === false) {
            return false;
        }
        $return = array();
        foreach (array_filter(array_unique(explode(",", $index))) as $name) {
            if (!$this->has($name, $cs)) {
                continue;
            }

            $value = $this->$method($name, $cs);
            if ($single) {
                $return = array('method' => $name, 'args' => $value);
                return true;
            }
            $return = array_merge($return, $value);
        }

        return true;
    }

    public function getOne($index, $caseSensitive = true)
    {
        if ($this->checkManyCalls($index, $caseSensitive, 'getOne', $return, true))  {
            return $return;
        }
        if (!$this->has($index, $caseSensitive)) {
            return array();
        }

        $return = array();
        $source = $caseSensitive ? $this->keys : $this->ikeys;
        if (!$caseSensitive) {
            $index = strtolower($index);
        }
        return $this->values[$source[$index][0]]['args'];
    }

    public function get($index, $caseSensitive = true)
    {
        if ($this->checkManyCalls($index, $caseSensitive, 'get', $return))  {
            return $return;
        }
        if (!$this->has($index, $caseSensitive)) {
            return array();
        }

        $return = array();
        $source = $caseSensitive ? $this->keys : $this->ikeys;
        if (!$caseSensitive) {
            $index = strtolower($index);
        }
        foreach ($source[$index] as $id) {
            $return[] = $this->values[$id];
        }
        return $return;
    }
}
