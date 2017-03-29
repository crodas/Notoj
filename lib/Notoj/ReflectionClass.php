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

/**
 *  @autoload("Notoj")
 */
class ReflectionClass extends \ReflectionClass
{
    protected $annotation = NULL;
    
    static protected $properties = array();
    static protected $methods = array();

    public function __construct($name) 
    {
        parent::__construct($name);
    }

    public function getAnnotations() 
    {
        if ($this->annotation === NULL) {
            $this->annotation = Notoj::parseDocComment($this, $this->getFileName());
        }
        return $this->annotation;
    }

    public function getInterfaces()
    {
        $interfaces = array();
        foreach (parent::getInterfaces() as $interface) {
            $interfaces[] = new self($interface->getName());
        }

        return $interfaces;
    }

    public function getMethods($filter = null)
    {
        $class = $this->name;
        if (array_key_exists($class, self::$methods)) {
            return self::$methods[$class];
        }

        $methods = array();
        if ($filter === null) {
            $methods = parent::getMethods();
        } else {
            $methods = parent::getMethods($filter);
        }
        foreach ($methods as $id => $method) {
            $methods[$id] = $this->getMethod($method->GetName());
        }
        self::$methods[$class] = $methods;

        return $methods;
    }

    public function getMethod($name)
    {
        return new ReflectionMethod($this->getName(), $name);
    }

    public function getParentClass()
    {
        $parent = parent::getParentClass();
        if ($parent) {
            $parent = new self($parent->getName());
        }
        return $parent;
    }

    public function getProperties($filter = null) 
    {
        $class = $this->name;
        if (array_key_exists($class, self::$properties)) {
            return self::$properties[$class];
        }

        $properties = array();
        if ($filter === null) {
            $properties = parent::getProperties();
        } else {
            $properties = parent::getProperties($filter);
        }
        foreach ($properties as $id => $property) {
            $properties[$id] = $this->getProperty($property->GetName());
        }
        self::$properties[$class] =  $properties;
        return $properties;
    }

    public function getProperty($name)
    {
        return new ReflectionProperty($this->getName(), $name);
    }
}

