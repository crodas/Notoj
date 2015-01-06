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

use RecursiveDirectoryIterator,
    DirectoryIterator,
    RecursiveIteratorIterator;

/**
 *  @autoload("File")
 */
class Dir extends Cacheable
{
    protected $filter;
    protected $cached;
    protected $cacheTs;
    protected $files = array();

    public function __construct($dirPath)
    {
        if (!is_dir($dirPath) || !is_readable($dirPath)) {
            throw new \RuntimeException("{$dirPath} is not a dir or cannot be read");
        }
        $this->dir = $dirPath;
        $this->filter = function(\splFileInfo $file) {
            return strtolower($file->getExtension()) === "php";
        };
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFilter(\Closure $callback)
    {
        $this->filter = $callback;
        return $this;
    }

    public function isCached()
    {
        return $this->cached;
    }

    public function getCacheTime()
    {
        return $this->cacheTs;
    }

    public function readDirectory($path)
    {
        $annotations = new Annotation\Set;
        $filter  = $this->filter;
        $modtime = filemtime($path);
        $cached  = Cache::get('dir://' . $path, $foo, $this->localCache);

        if ($cached && $cached['modtime'] >= $modtime) {
            if ($this->cacheTs < $modtime) {
                $this->cacheTs = $modtime;
            }
            foreach ($cached['cache'] as $annotation) {
                $obj = Annotation\Object::Instantiate($annotation['meta'], $annotation['data'], $annotations);
                $annotations[] = $obj;
                $this->files[] = $annotation['meta']['file'];
            }
            return $annotations;
        }
            
        $this->cached = false;
        $iter = new RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        foreach (new RecursiveIteratorIterator($iter) as $file) {
            if (!$file->isfile() || ($filter && !$filter($file))) {
                continue;
            }
            $this->files[] = realpath($file->getPathname());
            $file = new File($file->getPathname());
            $file->localCache = $this->localCache;
            $file->getAnnotations($annotations);
        } 

        $cache = $annotations->toCache();
        Cache::set('dir://' . $path, compact('modtime', 'cache'), $this->localCache);
        return $annotations;
    }

    public function getAnnotations(Annotation\Set $annotations = NULL)
    {
        $this->cached  = true;
        $this->cacheTs = 0;
        if (is_null($annotations)) {
            $annotations = new Annotation\Set;
        }

        $annotations->merge($this->readDirectory($this->dir));

        return $annotations;
    }
}
