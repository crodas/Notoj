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

abstract class Cacheable implements \IteratorAggregate 
{
    protected $annotations;
    protected $objs = array();

    public function getOne($name)
    {
        return $this->annotations->getOne($name);
    }


    public function get($name, $type = '')
    {
        $type = ucfirst(strtolower(trim($type)));
        return $type
            ? $this->GetAnnotationsBy($name, 'Notoj\TObject\z' . $type)
            : $this->annotations->get($name);
    }

    public function has($name)
    {
        return $this->annotations->has($name);
    }

    public function getIterator()
    {
        return $this->annotations;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function getByName($name, $type)
    {
        $name = strtolower($name);
        foreach ($this->objs as $obj) {
            if ($obj instanceof $type && $obj->getName() == $name) {
                return $obj;
            }
        }

        return NULL;
    }

    protected function getAnnotationsBy($filter, $class)
    {
        $anns = array();
        foreach ($this->objs as $obj) {
            if ($obj instanceof $class) {
                foreach ($obj->Get($filter) as $ann) {
                    $anns[] = $ann;
                }
            }
        }

        return $anns;
    }


    protected function getBy($filter, $class)
    {
        $objects = array();

        foreach ($this->objs as $obj) {
            if ($obj instanceof $class &&
                (!$filter || $obj->has($filter))) {
                $objects[] = $obj;
            }
        }

        return $objects;
    }

    public function getCallable($filter = '')
    {
        return $this->getBy($filter, 'Notoj\TObject\zCallable');
    }

    public function getFunctions($filter = '')
    {
        return $this->getBy($filter, 'Notoj\TObject\zFunction');
    }

    public function getMethods($filter = '')
    {
        return $this->getBy($filter, 'Notoj\TObject\zMethod');
    }

    public function getProperties($filter = '')
    {
        return $this->getBy($filter, 'Notoj\TObject\zProperty');
    }

    public function getClasses($filter = '')
    {
        return $this->getBy($filter, 'Notoj\TObject\zClass');
    }

    public function getPropertyByName($name)
    {
        return $this->getByName($name, 'Notoj\TObject\zProperty');
    }


    public function getMethodByName($name)
    {
        return $this->getByName($name, 'Notoj\TObject\zMethod');
    }

    public function getFunctionByName($name)
    {
        return $this->getByName($name, 'Notoj\TObject\zFunction');
    }

    public function getClassByName($name)
    {
        return $this->getByName($name, 'Notoj\TObject\zClass');
    }

}
