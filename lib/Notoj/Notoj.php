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

use Notoj\Annotation\Annotations;
use Notoj\Annotation\Annotation;

/**
 *  @Notoj
 */
class Notoj extends Cacheable
{
    const T_CLASS = 1;
    const T_FUNCTION = 2;
    const T_PROPERTY = 3;

    protected static $parsed = array();
    protected static $internal_cache = array();

    public static function parseDocComment($content)
    {
        if (is_object($content) && is_callable(array($content, 'getDocComment'))) {
            $content = $content->getDocComment();
        }
        $id = sha1($content);
        if (isset(self::$internal_cache[$id])) {
            $isCached = true;
            return unserialize(self::$internal_cache[$id]);
        }

        $pzToken = new Tokenizer($content);
        $Parser  = new \Notoj_Parser;
        $buffer  = array();
        $isNew   = true;

        while (true) {
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
        }
        try {
            $Parser->doParse(0, 0);
        } catch (\Exception $e) {
            // ignore error
        }
        $struct = new Annotations(array_merge($buffer, $Parser->body));
        self::$internal_cache[$id] = $struct->toCache();
        return $struct;
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
            self::$parsed[$file] = $parser->getAnnotations();
        }
        return self::$parsed[$file];
    }

}

