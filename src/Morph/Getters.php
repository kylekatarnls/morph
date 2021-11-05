<?php

declare(strict_types=1);

namespace Morph;

use Morph\Reflection\Method;
use ReflectionClass;
use ReflectionException;

class Getters extends MorphBase
{
    private array $prefixes;
    private string $pattern;

    public function __construct(array $prefixes = ['get'])
    {
        $this->prefixes = $prefixes;
    }

    /**
     * @param object|string $value
     * @throws ReflectionException
     */
    public function __invoke($value, ...$args): array
    {
        $result = [];
        $this->pattern ??= '/^(?:'. implode('|', $this->prefixes) . ')(?<name>[^a-z].*)/';
        $classReflection = new ReflectionClass($value);

        foreach ($classReflection->getMethods() as $method) {
            if (preg_match($this->pattern, $method->getName(), $match)) {
                $result[$match['name']] = new Method($method);
            }
        }

        return $result;
    }
}
