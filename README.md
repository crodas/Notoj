Notoj [![Build Status](https://secure.travis-ci.org/crodas/Notoj.png?branch=master)](http://travis-ci.org/crodas/Notoj)
=========================

Yet another annotation parser (DocBlocks). It's designed to be simple and intuitive. It extends the Reflection methods inside the Notoj namespace (ReflectionClass, RefletionProperties, ReflectionMethod so far), and adds the getAnnotations() method.



    use Notoj\ReflectionClass;
  
    /** @foo @bar */
    class Foo {
    }
  
    $reflection = new ReflectionClass('Foo');
    var_dump($reflection->getAnnotations());
  
The `getAnnotations()` returns an array, with a very simple array structure (`array("name" => "Foo", "args" 
=> NULL)`)

Format
-------
    /** @Foo */
    /** @Foo("some") */
    /** @Foo some other strings */
    /** @Foo(some_label="something here") */
    /** @Foo({some: "array here", arr:[1,2,3]}) */
    /** @Foo(some_label={some: "array here", arr:[1,2,3]}) */



Todo
-----
* Documentation

Feel free to email fork and submit pull requests or write me crodas@php.net
