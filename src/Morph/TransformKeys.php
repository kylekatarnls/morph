<?php

declare(strict_types=1);

namespace Morph;

class TransformKeys extends MorphBase
{
    private $transformer;

    public function __construct(callable $transformer)
    {
        $this->transformer = $transformer;
    }

    public function __invoke(array $value): array
    {
        return array_combine(
            $this->mapWithTransformer($this->transformer, array_keys($value)),
            $value,
        );
    }
}
