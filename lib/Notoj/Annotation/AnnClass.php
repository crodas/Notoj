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

use Notoj\Annotation,
    Notoj\Annotations,
    RuntimeException,
    InvalidArgumentException;

/**
 *  @autoload("Annotation")
 */
class AnnClass extends Annotation
{
    protected $parent;
    public function __construct(Array $args, Annotations $parent)
    {
        $this->parent = $parent;
        parent::__construct($args);
    }

    public function getProperties()
    {
        $classInfo = $this->parent->getClassInfo($this['class']);
        if (empty($classInfo['property'])) {
            return array();
        }
        return $classInfo['property'];
    }

    public function getParent()
    {
        if (empty($this['parent'])) {
            return NULL;
        }
        $classInfo = $this->parent->getClassInfo($this['parent']['class']);
        if (empty($classInfo)) {
            // This class has no annotation at all,
            // we will still create an 
            $args = $this['parent'];
            $ann = new self(array(), $this->parent);
            $ann->setMetadata($args);
            return $ann;
        }
        return $classInfo['class'];
    }

    public function getMethods()
    {
        $classInfo = $this->parent->getClassInfo($this['class']);
        if (empty($classInfo['method'])) {
            return array();
        }
        return $classInfo['method'];
    }
}
