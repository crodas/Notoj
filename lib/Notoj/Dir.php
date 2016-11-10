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

use crodas\FileUtil\Path;
use Notoj\Annotation\Annotations;
use Notoj\Annotation\Annotation;
use RecursiveDirectoryIterator;
use DirectoryIterator;
use RecursiveIteratorIterator;
use crodas\ClassInfo\ClassInfo;
use Remember\Remember;
use SplFileInfo;

/**
 *  @autoload("File")
 */
class Dir extends Cacheable
{
    protected $filter;
    protected $Parser;
    protected $dirs;

    public function __construct($dirPath, $parser = NULL)
    {
        $dirs = array();
        foreach ((array)$dirPath as $dir) {
            if (!is_dir($dir) || !is_readable($dir)) {
                throw new \RuntimeException("{$dir} is not a dir or cannot be read");
            }
            $dirs[] = $dir;
        }
        $this->dirs = $dirs;
        $this->filter = function(\splFileInfo $file) {
            return strtolower($file->getExtension()) === "php";
        };
        $this->Parser  = $parser ? $parser : new ClassInfo;
        $this->doParse();
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

    protected function addFile(File $file)
    {
        $this->annotations->merge($file->getAnnotations());
        $this->objs = array_merge($this->objs, $file->objs);

        return $file;
    }

    public function readDirectory($path)
    {
        $filter  = $this->filter;
        $parser  = $this->Parser;

        $wrap = Remember::wrap('notoj', function($dir, $files) use ($parser) {
            $files = array_filter($files, $dir[0]);
            $classes = array();
            foreach ($files as $file) {
                $classes[$file] = new File($file, $parser);
            }
            return serialize($classes);
        });

        foreach (unserialize($wrap(array('is_file', $path))) as $file => $obj) {
            if ($filter && !$filter(new SplFileInfo($file))) {
                continue;
            }
            $this->addFile($obj);
        }

        return $this->annotations;
    }

    protected function doParse()
    {
        $this->annotations = new Annotations;
        foreach ($this->dirs as $dir) {
            $this->readDirectory($dir);
        }
    }
}
