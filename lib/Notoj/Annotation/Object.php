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

class Object extends Base
{
    protected $args;
    protected $meta = array();


    public function getInstance($parent = NULL)
    {
        if (!empty($this->meta)) {
            return self::Instantiate($this->meta, $this->annotations ?: array(), $parent);
        }

        return $this;
    }

    public function hasAnnotation($name)
    {
        $name = strtolower($name);

        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() === $name) {
                return true;
            }
        }

        return false;
    }
    
    public static function Instantiate(Array $meta, Array $args, Set $parent = NULL)
    {
        if (!empty($meta['type'])) {
            throw new \RuntimeException;
            $class = '\\Notoj\\t' . ucfirst($meta['type']);
            if (class_exists($class)) {
                $obj = new $class($args, $parent);
            }
        }
        if (empty($obj)) {
            $obj = new self($args);
        }
        if (count($meta)) {
            $obj->setMetadata($meta);
        }

        return $obj;
    }

    public function __construct(Array $args = array())
    {
        foreach ($args as $arg) {
            $this->add($arg);
        }
        parent::__construct($args);
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
        return $this->meta;
    }

    public function getKeys()
    {
        return array_keys($this->annotationsByName);
    }

    public function __call($name, Array $args)
    {
        $zname = strtolower($name);
        if (substr($zname, 0, 2) == 'is') {
            $zname = substr($zname, 2);
            if (in_array($zname, array('property', 'class', 'function', 'method'))) {
                return false;
            }
        }

        throw new \BadMethodCallException("$name is not a valid method"); 
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

        if ($index[0] === '@') {
            return !empty($this->annotationsByName[$index]);
        }

        return false;
    }

    public function offsetGet($index)
    {
        if ($index === 'annotations') {
            // backwards compatiblility
            return $this;
        }

        if ($index[0] === '@') {
            return current($this->annotationsByName[$index]);
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
