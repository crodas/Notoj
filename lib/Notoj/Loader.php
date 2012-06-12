<?php

spl_autoload_register(function ($class) {
    /*
        This array has a map of (class => file)
    */
    static $classes = array (
  'Notoj\\Notoj' => '/Notoj.php',
  'Notoj\\Tokenizer' => '/Tokenizer.php',
  'Notoj_yyToken' => '/Parser.php',
  'Notoj_yyStackEntry' => '/Parser.php',
  'Notoj_Parser' => '/Parser.php',
  'Notoj\\ReflectionFunction' => '/ReflectionFunction.php',
  'Notoj\\ReflectionProperty' => '/ReflectionProperty.php',
  'Notoj\\ReflectionMethod' => '/ReflectionMethod.php',
  'Notoj\\ReflectionClass' => '/ReflectionClass.php',
  'Notoj\\ReflectionObject' => '/ReflectionObject.php',
);

    if (isset($classes[$class])) {
        require __DIR__  . $classes[$class];
        return true;
    }

    return false;
});
