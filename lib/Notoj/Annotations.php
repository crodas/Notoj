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

use ArrayObject,
    RuntimeException,
    InvalidArgumentException;

/**
 *  @autoload("Annotation")
 */
class Annotations extends ArrayObject
{
    protected $keys = array();
    protected $lastId = 0;

    public function offsetSet($index, $value)
    {
        if (!($value instanceof Annotation)) {
            throw new InvalidArgumentException("Annotations object only accept Annotation objects");
        }
        
        if ($index) {
            if ($this->offsetExists($index)) {
                throw new RuntimeException("You cannot modify annotations objects");
            }
            if (is_numeric($index)) {
                throw new InvalidArgumentException("Annotations object do not accept numeric index");
            }
        } else {
            $index = $this->lastId++;
        }

        foreach ($value->getKeys() as $key) {
            if (empty($this->keys[$key])) {
                $this->keys[$key] = array();
            }
            $this->keys[$key][] = $index;
        }

        parent::offsetSet($index, $value);
    }

    public function has($index)
    {
        return array_key_exists($index, $this->keys);
    }

    public function get($index)
    {
        if (!$this->has($index)) {
            return array();
        }

        $return = array();
        foreach ($this->keys[$index] as $id) {
            $return[] = $this->offsetGet($id);
        }
        return $return;
    }
}
