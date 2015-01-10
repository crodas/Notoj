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

class Notoj extends Cacheable
{
    const T_CLASS = 1;
    const T_FUNCTION = 2;
    const T_PROPERTY = 3;

    protected static $annotations = array();
    protected static $parsed = array();
    protected static $internal_cache = array();

    public static function enableCache($file) 
    {
        Cache::init($file);
    }

    public static function parseDocComment($content, &$isCached = NULL, $localCache = NULL) {
        if (is_object($content) && is_callable(array($content, 'getDocComment'))) {
            $content = $content->getDocComment();
        }
        $id = sha1($content);
        if (isset(self::$internal_cache[$id])) {
            $isCached = true;
            return self::$internal_cache[$id];
        }

        $isCached = false;
        $cached   = Cache::Get($id, $found, $localCache);
        if ($found) {
            $isCached = true;
            self::$internal_cache[$id] = $cached;
            return self::$internal_cache[$id];
        }
        $pzToken = new Tokenizer($content);
        $Parser  = new \Notoj_Parser;
        $buffer  = array();
        $isNew   = true;
        do {
            try {
                $token = $pzToken->getToken($isNew);
                if (!$token) break;
                $isNew = false;
                $Parser->doParse($token[0], $token[1]);
            } catch (\Exception $e) {
                $buffer = array_merge($buffer, $Parser->body);
                $Parser = new \Notoj_Parser;
                $isNew  = true;
            }
        } while(true);
        try {
            $Parser->doParse(0, 0);
        } catch (\Exception $e) {
            // ignore error
        }
        $struct = array_merge($buffer, $Parser->body);
        Cache::Set($id, $struct, $localCache);
        self::$internal_cache[$id] = $struct;
        return self::$internal_cache[$id];
    }

    public static function parseAll() 
    {
        $class = new self;
        foreach (get_included_files() as $file) {
            $class->parseFile($file);
        }
    }

    public function parseFile($file)
    {
        if (empty(self::$parsed[$file])) {
            $parser = new File($file);
            $parser->localCache = $this->localCache;
            self::$parsed[$file] = $parser->getAnnotations();
        }
        return self::$parsed[$file];
    }

}

