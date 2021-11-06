<?php

declare(strict_types=1);

namespace Morph;

class TransformValues extends MorphBase
{
    private $transformer;

    public function __construct(callable $transformer)
    {
        $this->transformer = $transformer;
    }

    public function __invoke(array $value): array
    {
        return $this->mapWithTransformer($this->transformer, $value);
    }
}
