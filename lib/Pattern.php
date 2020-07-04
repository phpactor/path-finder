<?php

namespace Phpactor\ClassFileConverter;

use Phpactor\ClassFileConverter\Exception\NoPlaceHoldersException;
use RuntimeException;
use Webmozart\PathUtil\Path;

class Pattern
{
    const TOKEN_REGEX = '{<([a-z-]+?)>}';

    /**
     * @var string
     */
    private $regex;

    /**
     * @var array
     */
    private $tokenNames;

    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $regex, string $pattern, array $tokenNames)
    {
        $this->regex = $regex;
        $this->tokenNames = $tokenNames;
        $this->pattern = $pattern;
    }

    public static function fromPattern(string $pattern): self
    {
        preg_match_all(self::TOKEN_REGEX, $pattern, $matches);

        [$tokens, $tokenNames] = $matches;

        $regex = $pattern;
        foreach (array_values($matches[0]) as $index => $token) {
            $greedy = $index + 1 !== count($tokenNames);
            $regex = strtr($regex, [$token => sprintf('(?%s%s+)', $token, $greedy ? '[^/]' : '.')]);
        }

        if (empty($tokenNames)) {
            throw new NoPlaceHoldersException(sprintf(
                'File pattern "%s" does not contain any <placeholders>',
                $pattern
            ));
        }

        return new self(sprintf('{%s$}', $regex), $pattern, $tokenNames);
    }

    public function fits(string $filePath): bool
    {
        return (bool)preg_match($this->regex, Path::canonicalize($filePath));
    }

    public function tokens(string $filePath): array
    {
        $filePath = Path::canonicalize($filePath);
        preg_match($this->regex, $filePath, $matches);
        return array_intersect_key($matches, array_combine($this->tokenNames, $this->tokenNames));
    }

    public function replaceTokens(array $tokens): string
    {
        return strtr($this->pattern, array_combine(array_map(function (string $key) {
            return '<' . $key . '>';
        }, array_keys($tokens)), array_values($tokens)));
    }

    public function toString(): string
    {
        return $this->pattern;
    }
}
