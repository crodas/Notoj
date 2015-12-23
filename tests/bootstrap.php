<?php
namespace Notoj\Test;

use Notoj\ReflectionClass,
    Notoj\ReflectionMethod;

require __DIR__ . "/../vendor/autoload.php";

if (!is_callable('_')) {
    function _($x) { return $x; }
}

function getReflection($class) 
{
    $class = explode("::", $class);
    return new ReflectionMethod($class[0], $class[1]);
}

@unlink(__DIR__ . "/tmp.cache");
