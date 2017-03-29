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

if (!class_exists('\PHPUnit_Framework_TestCase')) {
    require __DIR__ . '/phpunit-compat.php';
}

@unlink(__DIR__ . "/tmp.cache");
Remember::setDirectory('/tmp/cache');
