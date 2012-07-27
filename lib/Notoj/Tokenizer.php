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
 *  @autoload("Notoj", "\Notoj_Parser")
 */ 
class Tokenizer
{
    protected $body;
    protected $pos  = 0;
    protected $line = 0;
    protected $valid = false;
    protected $symbols = array(
        '@' => \Notoj_Parser::T_AT,
        ',' => \Notoj_Parser::T_COMMA,
        '(' => \Notoj_Parser::T_PAR_LEFT,
        ')' => \Notoj_Parser::T_PAR_RIGHT,
        '=' => \Notoj_Parser::T_EQ,
        '{' => \Notoj_Parser::T_CURLY_OPEN,
        '}' => \Notoj_Parser::T_CURLY_CLOSE,
        '[' => \Notoj_Parser::T_SUBSCR_OPEN,
        ']' => \Notoj_Parser::T_SUBSCR_CLOSE,
        ':' => \Notoj_Parser::T_COLON,
    );

    protected $keywords = array(
        'true'  => \Notoj_Parser::T_TRUE,
        'false' => \Notoj_Parser::T_FALSE,
    );


    public function __construct($body) {
        $this->body  = trim(substr($body, 3, -2));
    }

    protected function jumpNextLine()
    {
        $body = $this->body;
        $pos  = &$this->pos;
        $len  = strlen($body);
        if ($pos > 0) {
            $pos = strpos($body, "\n", $pos);
            if ($pos === false) {
                // EOF
                $pos = $len;
                return;
            }
        }
        
        $ignore = array(" ", "\t", "\r", "\n", "*");
        while ($pos < $len && in_array($body[$pos], $ignore)) {
            $pos++;
        }
    }

    public function getToken($start = true)
    {
        $symbols  = $this->symbols;
        $keywords = $this->keywords;

        if ($start) {
            $this->jumpNextLine();
        }

        $found = false;
        $body  = $this->body;
        $len   = strlen($body);
        for ($e = &$this->pos; !$found && $e < $len; $e++) {
            switch ($body[$e]) {
            case "\r": case " ": case "\f": case "\t":
                break;
            case "\n":
                $this->jumpNextLine();
                $e--;
                break;

            case '"': case "'":
                /* string {{{ */
                $end  = $body[$e];
                $data = "";
                while ($e < $len && $body[++$e] !== $end) {
                    if ($body[$e] == "\\") {
                        ++$e;
                    }
                    $data .= $body[$e];
                }
                if ($body[$e] !== $end) {
                    throw new \Exception("Unexpected end of line, expected {$end} in line {$body}");
                }
                $found = array(\Notoj_Parser::T_STRING, $data);
                break;
                /* }}} */

            default:
                if (!empty($symbols[$body[$e]])) {
                    $found = array($symbols[$body[$e]], $body[$e]);
                } else {
                    $data = "";
                    while ($e < $len && empty($symbols[$body[$e]])
                        && trim($body[$e]) !== "") {
                        $data .= $body[$e++];
                    }

                    if (empty($data)) {
                        continue;
                    }

                    $e--;
                    if (is_numeric($data[0]) && is_numeric($data)) {
                        $found = array(\Notoj_Parser::T_NUMBER, $data + 0);
                    } else if (!empty($keywords[strtolower($data)])) {
                        $found = array($keywords[strtolower($data)], $data);
                    } else {
                        $found = array(\Notoj_Parser::T_ALPHA, $data);
                    }
                }
                break;
            }
        }

        return $found;

        $found = false;
        for ($i=&$this->line; $i < count($this->lines); $i++) {
            $line  = $this->lines[$i];
            $len   = strlen($line);
            $found = false;
            for ($e = $this->pos; !$found && $e < $len; $e++) {
                if ($e == 0) {
                    // remove the junk
                    while ($e < $len && in_array($line[$e++], array('/', '*')));
                    if ($e >= $len) break;
                    $e--;
                }
                switch ($line[$e]) {
                case '\r': case ' ': case '\f': case '\t':
                    break;
                case '"': case "'":
                    $end  = $line[$e];
                    $data = "";
                    while ($e < $len && $line[++$e] !== $end) {
                        if ($line[$e] == "\\") {
                            ++$e;
                        }
                        $data .= $line[$e];
                    }
                    if ($line[$e] !== $end) {
                        throw new \Exception("Unexpected end of line, expected {$end} in line {$line}");
                    }
                    $found = array(\Notoj_Parser::T_STRING, $data);
                    break;
                default:
                    if (!empty($symbols[$line[$e]])) {
                        $found = array($symbols[$line[$e]], $line[$e]);
                    } else {
                        $data = "";
                        while ($e < $len && empty($symbols[$line[$e]])
                            && trim($line[$e]) !== "") {
                            $data .= $line[$e++];
                        }

                        if (empty($data)) {
                            continue;
                        }

                        $e--;

                        if (is_numeric($data[0]) && is_numeric($data)) {
                            $found = array(\Notoj_Parser::T_NUMBER, $data + 0);
                        } else if (!empty($keywords[strtolower($data)])) {
                            $found = array($keywords[strtolower($data)], $data);
                        } else {
                            $found = array(\Notoj_Parser::T_ALPHA, $data);
                        }
                    }
                    break;
                }
            }
            if ($found) {
                if (!$this->valid) {
                    if ($found[0] !== \Notoj_Parser::T_AT) {
                        $this->pos = 0;
                        continue;
                    }
                    $this->valid = true;
                }
                $this->pos = $e;
                break;
            }
            $this->pos = 0;
            $this->valid = false;
        }
        return $found;
    }

}

