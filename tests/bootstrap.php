<?php
use Notoj\ReflectionClass,
    Notoj\ReflectionMethod;

require __DIR__ . "/../lib/Notoj/Notoj.php";

\Notoj\Notoj::registerAutoloader();

function getReflection($class) 
{
    $class = explode("::", $class);
    return new ReflectionMethod($class[0], $class[1]);
}


