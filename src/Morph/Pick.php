<?php

declare(strict_types=1);

namespace Morph;

class Pick extends MorphBase
{
    public function __construct(private readonly string|int $key)
    {
    }

    public function __invoke($value)
    {
        return $value[$this->key] ?? null;
    }
}
