Notoj [![Build Status](https://secure.travis-ci.org/crodas/Notoj.png?branch=master)](http://travis-ci.org/crodas/Notoj)
=========================

Yet another annotation parser (DocBlocks). It's designed to be simple and intuitive. It extends the Reflection methods inside the Notoj namespace (ReflectionClass, RefletionProperties, ReflectionMethod so far), and adds the getAnnotations() method.

```php
<?php
use Notoj\ReflectionClass;
  
/** @foo @bar */
class Foo {
}
  
$reflection = new ReflectionClass('Foo');
var_dump($reflection->getAnnotations());
 ```
 
The `getAnnotations()` returns an array, with a very simple array structure (`array("method" => "Foo", "args" 
=> NULL)`)

How to use it
-------------

Notoj supports works under two scenearios, `offline` and `online` parsing.

### Online

Online parsing means that you want to retrieve annotations from an object, class or function that exists at run time. To use you'd need to use Reflection classes from the Notoj namespace.

### Offline

Offline parsing means that you want to get annotations from a file or directory and you rather not include them to use the `online` API. Notoj provides the `Notoj\File` and `\Notoj\Dir` classes to do that. They both return `Annotations` object which behaves as an array of `Annotation` (the output the `online` API), plus with a few extra things such as the `file`, `line`.


#### File
```php
<?php
$parser = new \Notoj\File("/foo.php");
$parser->getAnnotations();
```
#### Dir

```php
<?php
$parser = new \Notoj\Dir("/foo"); // The parser is recursive
$parser->setFilter(function($file) {
 return true;
});
$annotations = $parser->getAnnotations();
foreach ($annotations->get('Foo\Bar') as $annotations) {
   foreach ($annotations as $annotation) {
      var_dump(
          "found @Foo\Bar at " . $annotation['file'] 
          . ($annotation->isClass() ? ' on a class ' : ' on something else other than a class')
      );
   }
}
```

### Annotation object

It is the output object that represents an annotation. It is important that you notice that it does represents the annotations in a object, class or function and not a single annotation.

It provides `->get($tag)` and `->has($tag)` filters that helps processing the object.

```php
<?php
// check if there is a @Foo
if (!$annotation->has('Foo')) {
  throw new \RuntimeException("We were expecting a Foo annotation tag");
}

// ensure that *every* @Foo has at least some argument
foreach ($annotation->get('Foo') as $ann) {
   if (empty($ann['args'])) {
      throw new \RuntimeException("we were expecting arguments");
   }
}

foreach ($annotation as $ann) {
    // get *All* iterations
}
```

### Annotations object

It is an object that behaves like an array of Annotation object. Provides a very simple filter `->get($name)` and `->has($name)`.



Format
-------
```php
<?php
/** @Foo */
/** @Foo("some") */
/** @Foo some other strings */
/** @Foo(some_label="something here") */
/** @Foo({some: "array here", arr:[1,2,3]}) */
/** @Foo(some_label={some: "array here", arr:[1,2,3]}) */
```

Warning: Notoj will do its best effort to parse broken annotations, but if there is an error it will fail silently. Also, in the `@foo bar foobar` format whitespaces are ignored.
 
Caching support
---------------

Notoj supports caching that will help the Notoj's engine to avoid parsing over and over the same string. The cache will be invalidated automatically.

To enable this feature you need to specify which file should be used to save the cache, Notoj will do the rest :-).


```php
Notoj::enableCache("/tmp/annotations.php");
```


Todo
----
* Cache for offline parsing

Feel free to email fork and submit pull requests or write me crodas@php.net
