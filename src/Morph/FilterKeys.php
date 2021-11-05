<?php

declare(strict_types=1);

namespace Morph;

class FilterKeys extends MorphBase
{
    private $transformer;

    public function __construct(?callable $transformer)
    {
        $this->transformer = $transformer;
    }

    public function __invoke(array $value): array
    {
        return array_intersect_key($value, array_flip(
            array_filter(array_keys($value), $this->transformer),
        ));
    }
}
