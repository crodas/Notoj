<?php
use Notoj\ReflectionClass,
    Notoj\ReflectionMethod;

require __DIR__ . "/../lib/Notoj/autoload.php";
require __DIR__ . "/../vendor/autoload.php";

function getReflection($class) 
{
    $class = explode("::", $class);
    return new ReflectionMethod($class[0], $class[1]);
}


