<?php

namespace Phpactor\ClassFileConverter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\ClassFileConverter\Exception\NoMatchingSourceException;
use RuntimeException;

class PathFinderTest extends TestCase
{
    /**
     * @dataProvider provideTeleport
     */
    public function testTeleport(array $targets, $path, array $expectedTargets)
    {
        $teleport = PathFinder::fromDestinations($targets);
        $targets = $teleport->destinationsFor($path);

        $this->assertEquals($expectedTargets, $targets);
    }

    public function provideTeleport()
    {
        return [
            'no available targets' => [
                [
                    'target1' => 'lib/<kernel>.php',
                ],
                'lib/MyFile.php',
                [
                ],
            ],
            'one available target' => [
                [
                    'target1' => 'lib/<kernel>.php',
                    'target2' => 'tests/<kernel>Test.php',
                ],
                'lib/MyFile.php',
                [
                    'target2' => 'tests/MyFileTest.php',
                ],
            ],
            'multiple matching targets' => [
                [
                    'target1' => 'lib/<kernel>.php',
                    'target2' => 'tests/<kernel>Test.php',
                    'target3' => 'benchmarks/<kernel>Bench.php',
                ],
                'lib/MyFile.php',
                [
                    'target2' => 'tests/MyFileTest.php',
                    'target3' => 'benchmarks/MyFileBench.php',
                ],
            ],
            'composite path' => [
                [
                    'target1' => 'lib/<kernel>.php',
                    'target2' => 'tests/<kernel>Test.php',
                ],
                'lib/Foobar/Barfoo/MyFile.php',
                [
                    'target2' => 'tests/Foobar/Barfoo/MyFileTest.php',
                ],
            ],
            'absolute path' => [
                [
                    'target1' => 'lib/<kernel>.php',
                    'target2' => 'tests/<kernel>Test.php',
                ],
                '/home/daniel/lib/Foobar/Barfoo/MyFile.php',
                [
                    'target2' => 'tests/Foobar/Barfoo/MyFileTest.php',
                ],
            ],
            'relative path' => [
                [
                    'target1' => 'lib/<kernel>.php',
                    'target2' => 'tests/<kernel>Test.php',
                ],
                '/home/daniel/lib/Foobar/../Foobar/Barfoo/MyFile.php',
                [
                    'target2' => 'tests/Foobar/Barfoo/MyFileTest.php',
                ],
            ],
            'from unit test' => [
                [
                    'target1' => 'lib/<kernel>.php',
                    'target2' => 'tests/Unit/<kernel>Test.php',
                ],
                'tests/Unit/MyFileTest.php',
                [
                    'target1' => 'lib/MyFile.php',
                ],
            ],
        ];
    }

    public function testNoMatchingTarget()
    {
        $this->expectException(NoMatchingSourceException::class);
        $this->expectExceptionMessage('Could not find a matching pattern for path "/lib/Foo.php", known patterns: "/soos/<kernel>/boos.php"');

        $teleport = PathFinder::fromDestinations([
            'soos' => '/soos/<kernel>/boos.php',
        ]);

        $teleport->destinationsFor('/lib/Foo.php');
    }

    public function testDestinationWithNoKernel()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Destination "/soos/boos.php" contains no <kernel> placeholder');

        $teleport = PathFinder::fromDestinations([
            'soos' => '/soos/boos.php',
        ]);

        $teleport->destinationsFor('/lib/Foo.php');
    }
}
