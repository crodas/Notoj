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

class Annotation extends AnnotationBase
{
    protected $args;
    protected $meta = array();

    public function __construct(Array $args = array())
    {
        foreach ($args as $arg) {
            $this->add($arg['method'], $arg);
        }
        $this->annotations = $args;
        parent::__construct($args);
    }

    public function toCache()
    {
        return array('data' => $this->annotations, 'meta' => $this->meta);
    }

    public function setMetadata(Array $meta)
    {
        foreach (array_keys($meta) as $id) {
            if (is_numeric($id)) {
                throw new \RuntimeException("Metadata cannot contain numbers as keys");
            }
        }
        $this->meta = array_merge($this->meta, $meta);
    }

    public function getMetadata()
    {
        return $meta;
    }

    public function getKeys()
    {
        return array_keys($this->keys);
    }

    public function isClass()
    {
        $meta = $this->meta;
        return $meta['type'] == 'class';
    }

    public function isMethod()
    {
        $meta = $this->meta;
        return $meta['type'] == 'method';
    }

    public function isFunction()
    {
        $meta = $this->meta;
        return $meta['type'] == 'function';
    }

    public function isProperty()
    {
        $meta = $this->meta;
        return $meta['type'] == 'property';
    }

    public function getFile()
    {
        return $this->meta['file'];
    }


    public function offsetExists($index)
    {
        if ($index === 'annotations') {
            // backwards compatiblility
            return true;
        }

        if (array_key_exists($index, $this->meta)) {
            return true;
        }

        if (array_key_exists($index, $this->annotations)) {
            return true;
        }

        return false;
    }

    public function offsetGet($index)
    {
        if ($index === 'annotations') {
            // backwards compatiblility
            return $this;
        }

        if (array_key_exists($index, $this->meta)) {
            return $this->meta[$index];
        }

        if (array_key_exists($index, $this->annotations)) {
            return $this->annotations[$index];
        }

        return NULL;
    }

    public function offsetSet($index, $value)
    {
        throw new \RuntimeException("Annotation objects are read only");
    }

    public function getAll()
    {
        return $this->getIterator();
    }

}
