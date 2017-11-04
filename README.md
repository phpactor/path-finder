Path Finder
===========

[![Build Status](https://travis-ci.org/phpactor/path-finder.svg?branch=master)](https://travis-ci.org/phpactor/path-finder)

Library to infer paths from a given path where paths share the same "kernel"
(common section of the path).

For example, infer unit test path from the source file, from a unit test to
a bechmark, from the benchmark to the source file etc.

Usage
-----

Path finder accepts a hash map of destinations and their schemas. The
"kernel" is a place holder for the common path segment that all destinations
share:

```php
$pathFinder = PathFinder::fromDestinations([
    'source' => 'lib/<kernel>.php',
    'unit_test' => 'tests/Unit/<kernel>Test.php',
    'benchmark' => 'benchmarks/<kernel>Bench.php',
]);

$targets = $pathFinder->targetsFor('lib/Acme/Post.php');

var_dump($targets);
// [
//    'unit_test' => 'tests/Unit/Acme/PostTest.php',
//    'benchmark' => 'benchmarks/Acme/PostBench.php',
// ]
```
