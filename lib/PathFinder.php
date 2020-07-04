<?php

namespace Phpactor\ClassFileConverter;

use Phpactor\ClassFileConverter\Exception\NoMatchingSourceException;
use Webmozart\PathUtil\Path;

class PathFinder
{
    /**
     * @var array<string,Pattern>
     */
    private $destinations = [];

    private function __construct(array $destinations)
    {
        foreach ($destinations as $destinationName => $pattern) {
            $this->add($destinationName, $pattern);
        }
    }

    public static function fromDestinations(array $destinations): PathFinder
    {
        return new self($destinations);
    }

    /**
     * Return a hash map of destination names to paths representing
     * paths which relate to the given file path.
     *
     * @throws NoMatchingSourceException
     */
    public function destinationsFor(string $filePath): array
    {
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

    private function add($destinationName, $pattern): void
    {
        $this->destinations[$destinationName] = Pattern::fromPattern($pattern);
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
