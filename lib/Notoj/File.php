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

use Remember\Remember;
use crodas\FileUtil\Path;
use crodas\ClassInfo\ClassInfo;
use crodas\ClassInfo\Definition\TBase;
use crodas\ClassInfo\Definition\TClass;
use crodas\ClassInfo\Definition\TFunction;
use crodas\ClassInfo\Definition\TProperty;
use Notoj\Annotation\Annotations;
use Notoj\Annotation\Annotation;

class File extends Cacheable
{
    /**
     *  @type string
     */
    protected $files;
    protected $remember;
    protected $cached;
    protected $objAnnotation = array();
    protected static $fromCache;

    public function __construct($filePath, $parser = null)
    {
        if (self::$fromCache) return;
        $this->remember = Remember::init('notoj');
        $files = array();
        foreach ((array)$filePath as $file) {
            if (!is_file($file) || !is_readable($file)) {
                throw new \RuntimeException("{$filePath} is not a file or cannot be read");
            }
            $files[] = Path::normalize($file);
        }
        $this->files = $files;
        $this->annotations = new Annotations;

        foreach((array)$files as $file) {
            $this->doParse($file, $parser);
        }
    }

    protected function addObject(TBase $object)
    {
        $cache = strtolower(get_class($object) . '::' . $object->getName());
        if (!empty($object->class)) {
            $cache .= '::' . strtolower($object->class->getName());
        }

        if (empty($this->objs[$cache]))  {
            $obj  = Object\Base::create($object);
            $this->objs[$cache] = $obj;
            $this->objAnnotation[$cache] = $object;
            $this->annotations->merge($obj->getAnnotations());
        }
    }

    public static function fromCache($file, $str)
    {
        self::$fromCache = true;
        $self = new self($file);
        $self->path = $file;
        $self->annotations = new Annotations;
        foreach (unserialize($str) as $object) {
            $self->addObject($object);
        }
        self::$fromCache = false;;

        return $self;
    }

    public function toCache($filter = null)
    {
        $filter = $filter ?: function() { return true; };
        $toSerialize = array_filter($this->objAnnotation, $filter);
        return serialize($toSerialize);
    }

    public function isCached()
    {
        return $this->cached;
    }

    protected function doParse($path, $parser = null)
    {
        $cached = $this->remember->get($path, $isValid);
        if ($isValid) {
            $this->cached = true;
            foreach (unserialize($cached) as $object) {
                $this->addObject($object);
            }
            return;
        }

        $this->cached = false;

        try {
            $parser = $parser ? $parser : new ClassInfo;
            $parser->parse($path);
        } catch(\Exception $e) {
            // Internal error, probably parsing buggy/invalid php code
            return;
        }

        foreach ($parser->getPHPDocs() as $object) {
            if ($object->GetFile() == $path) {
                $this->addObject($object);
            }
        }

        $cache = $this->toCache(function($e) use ($path) {
            return $e->getFile() === $path;
        });
        $this->remember->store($path, $cache);
    }
}
