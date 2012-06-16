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
class File
{
    protected $path;
    protected $content;

    public function __construct($filePath = "")
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("{$filePath} is not a file or cannot be read");
        }
        $this->path    = $filePath;
        $this->content = file_get_contents($filePath); 
    }

    public function getAnnotations()
    {
        $tokens      = token_get_all($this->content);
        $allTokens   = count($tokens);
        $annotation  = NULL;
        $namespace   = "";
        $annotations = array();
        $traits      = defined('T_TRAIT') ? T_TRAIT : -1;

        $allow = array(T_WHITESPACE, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC);
        $level = 0;
        for($i=0; $i < $allTokens; $i++) {
            $token = $tokens[$i];
            if (!is_array($token) && $token != '{' && $token != '}') continue;
            switch ($token[0]) {
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
                $annotation = Notoj::parseDocComment($token[1]);
                $e = $i; /* copy the cursor */
                while (in_array($tokens[++$e][0], $allow));
                $token = $tokens[$e];
                switch ($token[0]) {
                case T_CLASS:
                case T_INTERFACE:
                case $traits:
                case T_FUNCTION:
                    while ($tokens[$e][0] != T_STRING) $e++;
                    if ($token[0] == T_FUNCTION) {
                        $def  = array(
                            'function' => $namespace . $tokens[$e][1],
                            'file'  => $this->path,
                            'line'  => $tokens[$e][2],
                        );
                        if (isset($classes[$level])) {
                            $def['function'] = $tokens[$e][1];
                            $def['class']    = $classes[$level];
                        }
                    } else {
                        $def  = array(
                            'class' => $namespace . $tokens[$e][1],
                            'file'  => $this->path,
                            'line'  => $tokens[$e][2],
                        );
                    }
                    $def['annotations'] = $annotation;
                    $annotations[] = $def;
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
        return $annotations;
    }
}
