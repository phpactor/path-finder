Path Finder
===========

![CI](https://github.com/phpactor/path-finder/workflows/CI/badge.svg)

Library to infer paths from a given path where paths share path segments.

For example, infer test paths for a given source file and vice-versa.

Usage
-----

Path finder accepts a hash map of destinations and their schemas. The
placeholders can be used to identify common parts of the path.

- The last placeholder is _greedy_ it will match all path segments until the
  suffix.
- Preceding placeholders will only match until the first path separator.

Examples
--------

### Navigating between test files

```php
$pathFinder = PathFinder::fromDestinations([
    'source' => 'lib/<kernel>.php',
    'unit_test' => 'tests/Unit/<kernel>Test.php',
    'benchmark' => 'benchmarks/<kernel>Bench.php',
]);

$targets = $pathFinder->targetsFor('lib/Acme/Post.php');

// [
//    'unit_test' => 'tests/Unit/Acme/PostTest.php',
//    'benchmark' => 'benchmarks/Acme/PostBench.php',
// ]
```

### Navigating between files organized by domain/module

```php
$pathFinder = PathFinder::fromDestinations([
    'source' => 'lib/<module>/<kernel>.php',
    'unit_test' => 'tests/<module>/Unit/<kernel>Test.php',
    'benchmark' => 'benchmarks/<module>/<kernel>Bench.php',
]);

$targets = $pathFinder->targetsFor('lib/MyModule/Acme/Post.php');

// [
//    'unit_test' => 'tests/MyModule/Unit/Acme/PostTest.php',
//    'benchmark' => 'benchmarks/MyModule/Acme/PostBench.php',
// ]
```

Contributing
------------

This package is open source and welcomes contributions! Feel free to open a
pull request on this repository.

Support
-------

- Create an issue on the main [Phpactor](https://github.com/phpactor/phpactor) repository.
- Join the `#phpactor` channel on the Slack [Symfony Devs](https://symfony.com/slack-invite) channel.

