<?php
use Notoj\ReflectionClass,
    Notoj\ReflectionMethod;

set_include_path('./lib/:' . get_include_path());

require "Notoj/Notoj.php";

function getReflection($class) 
{
    $class = explode("::", $class);
    return new ReflectionMethod($class[0], $class[1]);
}


