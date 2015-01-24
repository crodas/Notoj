# Notoj documentation

Notoj is an annotation reader for PHP. It was designed to read annotation from PHP at runtime (using Reflections) and by inspecting files.

## Introduction

Notoj can be used to get annotations of `classes`, `functions`, `methods` and `properties`. It can read by extending the `Reflection` classes from PHP.

```php
$reflection = new Notoj\Reflection("class");
foreach ($reflection->getAnnotations() as $annotation) {
    var_dump($annotation->getName(), $annotation->getArgs());
}
```

Notoj also can read annotations from files an directories. It gives flexibility and annotations can be used to discover classes/methods.

```php
$dir = new Notoj\Dir("/directory");
foreach ($dir->getClasses("foobar") as $class) {
    echo "{$class->getName()} has annotation @foobar\n";
}
```
## How to install

It can be installed using [composer](http://getcomposer.org/).

```bash
composer require crodas/notoj:"~1.0"
```

`Composer` will generate the autoloader automatically.

```php
require "vendor/autoload.php";

$dir = new Notoj\Dir(__DIR__ . '/models');
foreach ($dir->getClasses('Persist') as $persist) {
    // All the classes with @Persist
    var_dump($persist->getName(), $persist->getPath());
}
```

