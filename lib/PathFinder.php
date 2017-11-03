<?php

namespace Phpactor\ClassFileConverter;

use Phpactor\ClassFileConverter\Exception\NoMatchingDestinationException;
use Phpactor\ClassFileConverter\PathFinder;
use Webmozart\PathUtil\Path;
use RuntimeException;

class PathFinder
{
    const KERNEL = '<kernel>';

    /**
     * @var array
     */
    private $destinations = [];

    private function __construct(array $destinations)
    {
        $this->validateTargets($destinations);

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
     */
    public function destinationsFor(string $filePath): array
    {
        $filePath = Path::canonicalize($filePath);
        $source = $this->matchingSource($filePath);

        return $this->resolveDestinations($filePath, $source);
    }

    private function matchingSource($filePath)
    {
        foreach ($this->targets as $targetName => $pattern) {
            if ($this->matches($filePath, $pattern)) {
                return $targetName;
            }
        }

        throw new NoMatchingDestinationException(sprintf(
            'Could not find a matching pattern for path "%s", known patterns: "%s"',
            $filePath, implode('", "', $this->targets)
        ));
    }

    private function matches(string $filePath, $pattern)
    {
        $pattern = $this->pattern($pattern);

        return (bool) $this->matchPattern($filePath, $pattern);
    }

    private function resolveDestinations(string $filePath, $target)
    {
        $resolved = [];

        foreach ($this->targets as $targetName => $targetPattern) {
            if ($target === $targetName) {
                continue;
            }

            $sourcePattern = $this->pattern($this->targets[$target]);
            $kernel = $this->matchPattern($filePath, $sourcePattern);

            $resolved[$targetName] = str_replace(self::KERNEL, $kernel, $targetPattern);
        }

        return $resolved;
    }

    private function pattern(string $pattern): string
    {
        $pattern = preg_quote($pattern);

        return str_replace(preg_quote(self::KERNEL), '(.*?)', $pattern);
    }

    private function matchPattern(string $filePath, string $pattern)
    {
        if (preg_match('{' . $pattern . '$}', $filePath, $matches)) {
            return $matches[1];
        }

        return;
    }

    private function validateTargets(array $destinations)
    {
        foreach ($destinations as $destination) {
            if (strpos($destination, self::KERNEL)) {
                continue;
            }

            throw new RuntimeException(sprintf(
                'Destination "%s" contains no <kernel> placeholder',
                $destination
            ));
        }
    }

    private function add(string $destinationName, string $pattern)
    {
        $this->targets[$destinationName] = $pattern;
    }
}
