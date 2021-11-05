<?php

declare(strict_types=1);

namespace Morph;

class GettersToArray extends MorphBase
{
    private array $prefixes;
    private string $pattern;

    public function __construct(array $prefixes = ['get'])
    {
        $this->prefixes = $prefixes;
    }

    public function __invoke(object $value, ...$args): array
    {
        $result = [];
        $this->pattern ??= '/^(?:'. implode('|', $this->prefixes) . ')(?<name>[^a-z].*)/';

        foreach (get_class_methods($value) as $method) {
            if (preg_match($this->pattern, $method, $match)) {
                $name = $match['name'];
                $result[$name] = $value->$method(...$args);
            }
        }

        return $result;
    }
}
