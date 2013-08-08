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
 *  @autoload("Notoj", "Annotations")
 */
class File extends Cacheable
{
    /**
     *  @type string
     */
    protected $path;
    protected $cached;

    public function __construct($filePath)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("{$filePath} is not a file or cannot be read");
        }
        $this->path = realpath($filePath);
    }

    public function isCached()
    {
        return $this->cached;
    }

    public function getAnnotations(Annotations $annotations = NULL)
    {
        if (is_null($annotations)) {
            $annotations = new Annotations;
        }

        $modtime = filemtime($this->path);
        $cached = Cache::get('file://' . $this->path, $found, $this->localCache);

        if ($found && $cached['modtime'] >= $modtime) {
            $this->cached = true;
            foreach ((array)$cached['cache'] as $annotation) {
                $obj = Annotation::Instantiate($annotation['meta'], $annotation['data'], $annotations);
                $annotations[] = $obj;
            }
            return $annotations;
        }

        $this->cached = false;
        $content    = file_get_contents($this->path); 
        $tokens     = token_get_all($content);
        $allTokens  = count($tokens);
        $annotation = NULL;
        $namespace  = "";
        $traits     = defined('T_TRAIT') ? T_TRAIT : -1;

        $allow = array(
            T_WHITESPACE, T_PUBLIC, T_PRIVATE, T_PROTECTED, 
            T_STATIC, T_ABSTRACT, T_FINAL
        );
        

        $level = 0;
        $cache = array();
        for($i=0; $i < $allTokens; $i++) {
            $token = $tokens[$i];
            if (!is_array($token) && $token != '{' && $token != '}') continue;

            switch ($token[0]) {
            case T_CURLY_OPEN:
            case '{':
                $level++;
                break;
            case '}':
                $level--;
                break;
            case T_CLASS:
            case T_INTERFACE:
            case $traits:
                while ($tokens[$i][0] != T_STRING) $i++;
                $name = $namespace . $tokens[$i][1];
                $classes[$level+1] = $name;
                break;

            case T_DOC_COMMENT:
                $annotation = Notoj::parseDocComment($token[1], $foo, $this->localCache);
                $e = $i; /* copy the cursor */
                while (in_array($tokens[++$e][0], $allow));
                $token = $tokens[$e];

                switch ($token[0]) {
                case T_VARIABLE:
                    if (!isset($classes[$level])) {
                        break;
                    }

                    $visibility = array();
                    for ($x=$e-1; $x > 0; $x--) {
                        if (!in_array($tokens[$x][0], $allow)) break;
                        switch ($tokens[$x][0]) {
                        case T_PUBLIC:
                        case T_PRIVATE:
                        case T_STATIC:
                        case T_PROTECTED:
                            $visibility[] = substr(strtolower(token_name($tokens[$x][0])), 2);
                            break;
                        }
                    }

                    $annotation->setMetadata(array(
                        'type'     => 'property',
                        'property' => substr($token[1],1),
                        'class'  => $classes[$level],
                        'file'   => $this->path,
                        'line'   => $tokens[$e][2],
                        'visibility' => $visibility,
                    ));
                    $annotations[] = $annotation->getInstance($annotations);
                    $cache[] = $annotation->toCache();
                    break;
                case T_CLASS:
                case T_INTERFACE:
                case $traits:
                case T_FUNCTION:

                    $visibility = array();
                    for ($x=$e-1; $x > 0; $x--) {
                        if (!in_array($tokens[$x][0], $allow)) break;
                        switch ($tokens[$x][0]) {
                        case T_PUBLIC:
                        case T_PRIVATE:
                        case T_STATIC:
                        case T_PROTECTED:
                        case T_ABSTRACT:
                        case T_FINAL:
                            $visibility[] = substr(strtolower(token_name($tokens[$x][0])), 2);
                            break;
                        }
                    }

                    while ($tokens[$e][0] != T_STRING) $e++;
                    if ($token[0] == T_FUNCTION) {
                        $def  = array(
                            'type'     => 'function',
                            'function' => $namespace . $tokens[$e][1],
                            'file'  => $this->path,
                            'line'  => $tokens[$e][2],
                        );

                        if (isset($classes[$level])) {
                            $def['type']     = 'method';
                            $def['function'] = $tokens[$e][1];
                            $def['class']    = $classes[$level];
                        }
                    } else {
                        $def  = array(
                            'type'  => 'class',
                            'class' => $namespace . $tokens[$e][1],
                            'file'  => $this->path,
                            'line'  => $tokens[$e][2],
                        );
                    }

                    $def['visibility'] = $visibility;
                    $annotation->setMetadata($def);
                    $annotations[] = $annotation->getInstance($annotations);
                    $cache[] = $annotation->toCache();
                    break;
                }
                break;

            case T_NAMESPACE:
                while ($tokens[$i][0] != T_STRING && $tokens[$i] != '{') $i++;
                $parts = array();
                while ($tokens[$i][0] == T_STRING || $tokens[$i][0] == T_NS_SEPARATOR || $tokens[$i][0] == T_WHITESPACE) {
                    if ($tokens[$i][0] != T_WHITESPACE) {
                        $parts[] = $tokens[$i][1];
                    }
                    $i++;
                }
                $namespace = empty($parts) ? "" : implode("", $parts) . '\\';
                break;
            }
        }

        $cached = Cache::set('file://' . $this->path, compact('modtime', 'cache'), $this->localCache);
        return $annotations;
    }
}
