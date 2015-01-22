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

use crodas\ClassInfo\ClassInfo;
use crodas\ClassInfo\Definition\TClass;
use crodas\ClassInfo\Definition\TFunction;
use crodas\ClassInfo\Definition\TProperty;

class File extends Cacheable
{
    /**
     *  @type string
     */
    protected $path;
    protected $cached;

    public function __construct($filePath, $localCache = null)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("{$filePath} is not a file or cannot be read");
        }
        $this->path = realpath($filePath);
        $this->localCache  = $localCache;
        $this->doParse();
    }

    public function toCache()
    {
        return $this->annotations->toCache();
    }

    public function isCached()
    {
        return $this->cached;
    }

    protected function doParse()
    {
        $modtime = filemtime($this->path);
        $cached = Cache::get('file://' . $this->path, $found, $this->localCache);

        $annotations = new Annotations;

        if ($found && $cached['modtime'] >= $modtime) {
            $this->cached = true;
            die('cache');
            foreach ((array)$cached['cache'] as $annotation) {
                $annotations[] = $obj;
            }
            return $annotations;
        }

        $this->cached = false;

        try {
            $parser = new ClassInfo($this->path);
        } catch(\Exception $e) {
            // Internal error, probably parsing buggy/invalid php code
            return $annotations;
        }

        $cache = array();
        foreach ($parser->getPHPDocs() as $object) {
            $type = __NAMESPACE__ . '\Object\z' . substr(strstr(get_class($object), "\\T"), 2);
            $annotations   = Notoj::parseDocComment($object->GetPHPDoc(), $foo, $this->localCache);
            $this->objs[]  = new $type($object, $annotations);
            $this->annotations[] = $annotations;
        }

        $cached = Cache::set('file://' . $this->path, compact('modtime', 'cache'), $this->localCache);
        return $annotations;
    }
}
