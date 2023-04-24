<?php

declare(strict_types=1);

namespace Morph;

class FilterValues extends MorphBase
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
        return array_filter($value, $this->transformer, $this->mode);
    }
}
