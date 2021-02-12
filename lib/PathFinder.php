<?php

namespace Phpactor\ClassFileConverter;

use Phpactor\ClassFileConverter\Exception\NoMatchingSourceException;
use Webmozart\PathUtil\Path;

class PathFinder
{
    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var array<string,Pattern>
     */
    private $destinations = [];

    /**
     * @param string $projectRoot
     * @param array<string, Pattern> $destinations
     */
    private function __construct($projectRoot, array $destinations)
    {
        $this->projectRoot = $projectRoot;
        $this->destinations = $destinations;
    }

    /**
     * @param string $projectRoot
     * @param array<string, string> $destinations
     */
    public static function fromDestinations($projectRoot, array $destinations): PathFinder
    {
        return new self($projectRoot, array_map(function (string $pattern) {
            return Pattern::fromPattern($pattern);
        }, $destinations));
    }

    /**
     * Return a hash map of destination names to paths representing
     * paths which relate to the given file path.
     *
     * @throws NoMatchingSourceException
     * @return array<string,string>
     */
    public function destinationsFor(string $filePath): array
    {
        if (strpos($filePath, $this->projectRoot) === 0) {
            $length = strlen($this->projectRoot);
            $removeSlash = substr($this->projectRoot, -1) !== '/' ? 1 : 0;
            $filePath = substr($filePath, $length + $removeSlash); // +1 to avoid leading slash
        }

        $destinations = [];
        $sourcePattern = $this->findSourcePattern($filePath);

        foreach ($this->destinations as $name => $pattern) {
            assert($pattern instanceof Pattern);
            if ($pattern === $sourcePattern) {
                continue;
            }

            $tokens = $sourcePattern->tokens($filePath);
            $destinations[$name] = $pattern->replaceTokens($tokens);
        }

        return $destinations;
    }

    private function findSourcePattern(string $filePath): Pattern
    {
        foreach ($this->destinations as $name => $pattern) {
            assert($pattern instanceof Pattern);
            if ($pattern->fits($filePath)) {
                return $pattern;
            }
        }

        throw new NoMatchingSourceException(sprintf(
            'Could not find matching source pattern for "%s", known patterns: "%s"',
            $filePath,
            implode('", "', array_map(function (Pattern $pattern) {
                return $pattern->toString();
            }, $this->destinations))
        ));
    }
}
