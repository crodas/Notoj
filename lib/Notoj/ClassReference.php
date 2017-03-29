<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2016 César Rodas                                                  |
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

use PhpParser\ParserFactory;
use PhpParser;
use PhpParser\Node\Stmt;
use PhpParser\Node;

class NamespaceVisitor extends PhpParser\NodeVisitorAbstract
{
    public $classes = array();
    public $namespace;

    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Use_) {
            foreach ($node->uses as $class) {
                $this->classes[$class->alias] = (string)$class->name;
            }
        }
        if ($node instanceof Stmt\Namespace_) {
            $this->namespace = (string)$node->name;
        }
    }
}

class ClassReference
{
    public static function resolve($class, $file)
    {
        if ($class[0] === '\\') {
            return $class;
        }

        ini_set('xdebug.max_nesting_level', 3000);
        if (class_exists('PhpParser\Parser')) {
            // php-parser version 1
            $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative);
        } else {
            // php-parser version 2
            $parser = new ParserFactory;
            $parser = $parser->create(ParserFactory::PREFER_PHP7);
        }

        $traverser = new PhpParser\NodeTraverser;
        $visitor   = new NamespaceVisitor;
        $traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver); // we will need resolved names
        $traverser->addVisitor($visitor);

        try {
            $stmts = $parser->parse(file_get_contents($file));
            $traverser->traverse($stmts);
        } catch (\Exception $e) {
            return NULL;
        }

        $parts = array_values(array_filter(explode("\\", $class)));
        if (!empty($visitor->classes[$parts[0]])) {
            $parts[0] = $visitor->classes[$parts[0]];
            return implode("\\", $parts);
        }

        return implode("\\", array_filter(array_merge(array($visitor->namespace), $parts)));
    }
}
