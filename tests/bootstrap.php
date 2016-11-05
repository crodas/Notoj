<?php
namespace Notoj\Test;

use Notoj\ReflectionClass;
use Notoj\ReflectionMethod;
use Remember\Remember;

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
Remember::setDirectory('/tmp/cache');
