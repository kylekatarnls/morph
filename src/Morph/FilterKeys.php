<?php

declare(strict_types=1);

namespace Morph;

class FilterKeys extends MorphBase
{
    private $transformer;

    private int $mode;

    public function __construct(
        ?callable $transformer,
        int $mode = 0
    ) {
        $this->transformer = $transformer;
        $this->mode = $mode;
    }

    public function __invoke(array $value): array
    {
        return array_intersect_key($value, array_flip(
            array_filter(array_keys($value), $this->transformer, $this->mode),
        ));
    }
}
