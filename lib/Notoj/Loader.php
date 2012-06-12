<?php
$call = 0;
$load = 0;
spl_autoload_register(function ($class) use (&$call, &$load) {
    /*
        This array has a map of (class => file)
    */
    static $classes = array (
  'notoj\\notoj' => '/Notoj.php',
  'notoj\\tokenizer' => '/Tokenizer.php',
  'notoj_yytoken' => '/Parser.php',
  'notoj_yystackentry' => '/Parser.php',
  'notoj_parser' => '/Parser.php',
  'notoj\\reflectionfunction' => '/ReflectionFunction.php',
  'notoj\\reflectionproperty' => '/ReflectionProperty.php',
  'notoj\\reflectionmethod' => '/ReflectionMethod.php',
  'notoj\\reflectionclass' => '/ReflectionClass.php',
  'notoj\\reflectionobject' => '/ReflectionObject.php',
);
    static $deps    = array (
);

    $class = strtolower($class);
    if (isset($classes[$class])) {
        $call++;
        $load++;
        if (!empty($deps[$class])) {
            foreach ($deps[$class] as $zclass) {
                if (!class_exists($zclass, false) && !interface_exists($zclass, false)) {
                    $load++;
                    require __DIR__  . $classes[$zclass];
                }
            }
        }

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            require __DIR__  . $classes[$class];
        }
        return true;
    }

    return false;
});

