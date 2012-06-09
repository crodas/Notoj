%name Notoj_
%include {
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
}

%declare_class {class Notoj_Parser }
%include_class {
    public $body = array();
}

%syntax_error {
    $expect = array();
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = self::$yyTokenName[$token];
    }
    throw new Exception('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. ')');
}


%left T_COMMA.

start ::= body. 

body ::= body code(C). { $this->body[] = C; }
body ::= . { $this->body = array(); }

code(A) ::= T_AT T_ALPHA(B) args(C) . { A = array('method' => trim(B), 'args' => C); }

args(A) ::= T_PAR_LEFT args_body(C) T_PAR_RIGHT . { A = C; }
args(A) ::= term_array(B) . { A = array(implode(' ', B)); }
args(A) ::= . { A = NULL; }

term_array(A) ::= term_array(B) term(C) . { A = B; A[] = C; }
term_array(A) ::= . { A = array(); }

args_body(A) ::= args_body(B) T_COMMA args_body(C) . {  A = array_merge(B, C); }
args_body(A) ::= expr(C) . { A = array(C); }
args_body(A) ::= named_arg(C) . { A = C; }
args_body(A) ::= . { A = array(); }

named_arg(A) ::= term(B) T_EQ expr(C) . { A = array(B => C); }


/* some day we might care about expressions rather than term */
expr(A) ::= term(B) . { A = B; }
expr(A) ::= json(B) . { A = B; }

term(A) ::= T_ALPHA(B)  . { A = trim(B); }
term(A) ::= T_TRUE      . { A = TRUE; }
term(A) ::= T_FALSE     . { A = FALSE; }
term(A) ::= T_STRING(B) . { A = B; }
term(A) ::= T_NUMBER(B) . { A = B; }

/* json {{{ */
json(A) ::= T_CURLY_OPEN json_obj(B) T_CURLY_CLOSE. { A  = B; }
json(A) ::= T_SUBSCR_OPEN json_arr(B) T_SUBSCR_CLOSE. { A = B; }

json_obj(A) ::= json_obj(B) T_COMMA json_obj(C). { A = array_merge(B, C); }
json_obj(A) ::= term(B) T_COLON expr(C) . { A = array(B => C); } 

json_arr(X) ::= json_arr(A) T_COMMA expr(B) .  { X = A; X[] = B; }
json_arr(A) ::= expr(B). { A = array(B); }
/* }}} */
