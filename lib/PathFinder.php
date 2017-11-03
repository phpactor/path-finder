<?php

namespace Phpactor\ClassFileConverter;

use Phpactor\ClassFileConverter\Exception\NoMatchingTargetException;
use Phpactor\ClassFileConverter\PathFinder;

class PathFinder
{
    const KERNEL = '<kernel>';

    /**
     * @var array
     */
    private $targets;

    private function __construct(array $targets)
    {
        $this->targets = $targets;
    }

    public static function fromTargets(array $targets): PathFinder
    {
        return new self($targets);
    }

    public function targetsFor(string $filePath)
    {
        $target = $this->matchingTarget($filePath);

        return $this->resolveRemainingTargets($filePath, $target);
    }

    private function matchingTarget($filePath)
    {
        foreach ($this->targets as $targetName => $pattern) {
            if ($this->matches($filePath, $pattern)) {
                return $targetName;
            }
        }

        throw new NoMatchingTargetException(sprintf(
            'Could not find a matching pattern for path "%s", known patterns: "%s"',
            $filePath, implode('", "', $this->targets)
        ));
    }

    private function matches(string $filePath, $pattern)
    {
        $pattern = $this->pattern($pattern);

        return (bool) preg_match('{' . $pattern . '$}', $filePath);
    }

    private function resolveRemainingTargets(string $filePath, $target)
    {
        $resolved = [];

        foreach ($this->targets as $targetName => $targetPattern) {
            if ($target === $targetName) {
                continue;
            }

            $targetPattern = $this->pattern($targetPattern);
            $sourcePattern = $this->pattern($this->targets[$target]);

            $resolved[$targetName] = preg_replace('{' . $sourcePattern . '$}', $targetPattern, $filePath);
        }

        return $resolved;
    }

    private function pattern(string $pattern): string
    {
        $pattern = preg_quote($pattern);
        return str_replace(preg_quote(self::KERNEL), '(.*?)', $pattern);
    }
}
