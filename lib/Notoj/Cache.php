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

use crodas\FileUtil\File;

class Cache
{
    protected static $isDirty = array();
    protected static $data = array();
    protected static $path = array();
    protected static $path2ns = array();
    protected static $registredShutdown = false;

    const CACHE_VERSION = "0.1";

    public static function init($filepath, $global = true)
    {
        if (!is_file($filepath)) {
            if (!file_put_contents($filepath, "<?php\n", LOCK_EX)) {
                throw new \RuntimeException("Cannot write file {$filepath}");
            }
        }

        if (isset(self::$path2ns[$filepath])) {
            return self::$path2ns[$filepath];
        }

        $ns     = $global ? 'global' : uniqid(true);
        $data   = (array)require($filepath);
        $useful = !empty($data['vesion']) && $data['version'] == self::CACHE_VERSION;
        self::$data[$ns]    = $useful ? $data['data'] : array();
        self::$path[$ns]    = $filepath;
        self::$isDirty[$ns] = false;

        self::$path2ns[$filepath] = $ns;

        if (!self::$registredShutdown) {
            register_shutdown_function(function() {
                Cache::save();
            });
            self::$registredShutdown = true;
        }
        return $ns;
    }

    public static function get($key, &$has = NULL, $ns = 'global')
    {
        $ns = $ns ?: 'global';
        if (empty(self::$data[$ns]) || !array_key_exists($key, self::$data[$ns])) {
            $has = false;
            return NULL;
        }
        $has = true;
        return self::$data[$ns][$key];
    }

    public static function set($key, $value, $ns = 'global')
    {
        $ns = $ns ?: 'global';
        if (empty(self::$path[$ns])) {
            return false;
        }
        if (is_object($value) && is_callable(array($value, 'toCache'))) {
            $value = $value->toCache();
        }
        self::$data[$ns][$key] = $value;
        self::$isDirty[$ns]    = true;
        return true;
    }

    public static function Save()
    {
        $i = 0;
        foreach (self::$path as $ns => $file) {
            if (!self::$isDirty[$ns]) continue;
            self::$isDirty[$ns] = false;
            File::dumpArray($file, array(
                "version" => self::CACHE_VERSION, 
                "data" => self::$data[$ns]
            ));
            $i++;
        }
        return $i;
    }

    public static function debug(){
        //var_dump(self::$path, self::$data);exit;
    }

}
