Source Teleport
===============

[![Build Status](https://travis-ci.org/phpactor/teleport.svg?branch=master)](https://travis-ci.org/phpactor/teleport)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpactor/teleport/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpactor/teleport/?branch=master)

Teleport between source code dimensions. For example, provide the file path
for the unit test for file `/src/Foobar/Barfoo.php` and from the unit test
back, or to new, unexplored dimensions.

Usage
-----

```php
$teleporter = Teleporter::fromTargets([
    'source' => 'lib/<kernel>.php',
    'unit_test' => 'tests/Unit/<kernel>Test.php',
    'benchmark' => 'benchmarks/<kernel>Bench.php',
]);

$targets = $teleporter->teleporter->targetsFor('lib/Acme/Post.php');

var_dump($targets);
// [
//    'unit_test' => 'tests/Unit/Acme/PostTest.php',
//    'benchmark' => 'benchmarks/Acme/PostBench.php',
// ]
```
