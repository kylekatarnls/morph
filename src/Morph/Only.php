<?php

declare(strict_types=1);

namespace Morph;

class Only extends MorphBase
{
    private array $keys;

    public function __construct($keys)
    {
        $this->keys = (array) $keys;
    }

    public function __invoke(array $value): array
    {
        return array_intersect_key($value, array_flip($this->keys));
    }
}
