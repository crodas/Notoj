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
 *  @autoload("Annotation")
 */
class Notoj
{
    const T_CLASS = 1;
    const T_FUNCTION = 2;
    const T_PROPERTY = 3;

    protected static $annotations = array();
    protected static $parsed = array();

    public static function registerAutoloader() {
        require __DIR__ . "/autoload.php";
    }

    public static function enableCache($file) 
    {
        Cache::init($file);
    }

    public static function parseDocComment($content, &$isCached = NULL) {
        if (is_object($content) && is_callable(array($content, 'getDocComment'))) {
            $content = $content->getDocComment();
        }
        $id = sha1($content);
        $isCached = false;
        $cached   = Cache::Get($id, $found);
        if ($found) {
            $isCached = true;
            return Annotation::Instantiate(array(), $cached);
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
        Cache::Set($id, $struct);
        return Annotation::Instantiate(array(), $struct);
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
            self::$parsed[$file] = $this->parse(file_get_contents($file));
        }
        return self::$parsed[$file];
    }

    public static function query($name) 
    {
        if (empty(self::$annotations[$name])) {
            return array();
        }
        return self::$annotations[$name];
    }

    public function parse($string)
    {
        $tokens = token_get_all($string);
        $total  = count($tokens);
        $nodes  = array();
        $classes = array();
        $level  = 0;
        $namespace = "\\";
        for ($i=0; $i < $total; $i++) {
            $token = $tokens[$i];

            switch ($token[0]) {
            case T_NAMESPACE:
                $i += 2;
                $namespace = "\\" . $tokens[$i][1] . "\\";
                break;

            case T_CLASS:
            case T_INTERFACE:
                for (; $i < $total; $i++) {
                    switch ($tokens[$i][0]) {
                    case T_STRING:
                        $classes[] = array($namespace . $tokens[$i][1], $level);
                        break 2;
                    }
                }
                break;

            case '{':
                $level++;
                break;

            case '}':
                $level--;
                if (count($classes) > 0) {
                    if ($classes[count($classes) - 1][1] ==  $level) {
                        array_pop($classes);
                    }
                }

                break;

            case T_DOC_COMMENT:
                $type = NULL;
                $name = "";
                $zdoc = self::parseDocComment($token[1]);
                if (count($zdoc) == 0) {
                    continue;
                }
                for ($i++; $i < $total; $i++) {
                    if (!is_array($tokens[$i])) { --$i; break; }
                    switch ($tokens[$i][0]) {
                    case T_FUNCTION:
                        $type = self::T_FUNCTION;
                        break;
                    case T_CLASS:
                    case T_INTERFACE:
                        $type = self::T_CLASS;
                        break;
                    case T_VARIABLE:
                        $name = $tokens[$i][1];
                        $type = self::T_PROPERTY;
                        break;

                    case T_STRING:
                        $name = $tokens[$i][1];
                        if ($type == self::T_CLASS) {
                            $classes[] = array($namespace . $name, $level);
                        }
                        break;
                    case T_WHITESPACE:
                    case T_PROTECTED:
                    case T_STATIC:
                    case T_PUBLIC:
                        /* ignored */
                        break;
                    default:
                        --$i;
                        break 2;
                    }
                }

                switch ($type){
                case self::T_FUNCTION:
                    if (count($classes) > 0) {
                        $class = $classes[count($classes) - 1];
                        $node = new ReflectionMethod($class[0], $name);
                    } else {
                        $node = new ReflectionFunction($namespace . $name);
                    }
                    break;
                case self::T_PROPERTY:
                    if (count($classes) > 0) {
                        $class = $classes[count($classes) - 1];
                        $node = new ReflectionProperty($class[0], substr($name,1));
                    }
                    break;
                case self::T_CLASS:
                    $node = new ReflectionClass($namespace . $name);
                    break;
                }
                if (!empty($node)) {
                    $nodes[] = $node;
                    foreach ($zdoc as  $doc) {
                        $name = $doc['method'];
                        if (empty(self::$annotations[$name])) {
                            self::$annotations[$name] = array();
                        }
                        self::$annotations[$name][] = $node;
                    }
                    unset($node);
                }
                break;
            }
        }
        return $nodes;
    }
}

