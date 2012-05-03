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


class Notoj
{
    public static function parseDocComment($content) {
        $pzToken = new Tokenizer($content);
        $Parser  = new \Notoj_Parser;
        $buffer  = array();
        do {
            $token = $pzToken->getToken();
            if ($token) {
                try {
                    $Parser->doParse($token[0], $token[1]);
                } catch (\Exception $e) {
                    $buffer = array_merge($buffer, $Parser->body);
                    $Parser = new \Notoj_Parser;
                }
            }
        } while($token);
        $Parser->doParse(0, 0);
        return array_merge($buffer, $Parser->body);
    }

    public function parseFile($file)
    {
        return $this->parse(file_get_contents($file));
    }

    public function parse($string)
    {
        $tokens = token_get_all($string);
        $total  = count($tokens);
        $zAnnot = false;
        $nodes  = array();
        for ($i=0; $i < $total; $i++) {
            $token = $tokens[$i];
            if ($token[0] == T_DOC_COMMENT) {
                $zAnnot = self::parseDocComment($token[1]);
            } else if ($zAnnot) {
                $node = new Node;
                for (; $i < $total; $i++) {
                    if (!is_array($tokens[$i])) break;
                    switch ($tokens[$i][0]) {
                    case T_PROTECTED:
                        $node->flags[] = 'protected';
                        break;
                    case T_FUNCTION:
                        $node->type = Node::T_FUNCTION;
                        break;
                    case T_CLASS:
                        $node->type = Node::T_CLASS;
                        break;
                    case T_VARIABLE:
                        $node->name = $tokens[$i][1];
                        $node->type = Node::T_PROPERTY;
                        break;
                    case T_STRING:
                        $node->name = $tokens[$i][1];
                        break;
                    }
                }
                $node->annotations = $zAnnot;
                $nodes[] = $node;
                $zAnnot = false;
            }
        }
        print_r($nodes);exit;
    }
}

