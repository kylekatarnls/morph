<?php

declare(strict_types=1);

namespace Morph;

class Sequence extends MorphBase
{
    protected array $transformers;

    public function __construct(array $transformers = [])
    {
        $this->transformers = $transformers;
    }

    public function __invoke(...$args)
    {
        return array_reduce($this->getTransformer(), static function (array $args, $transformer) {
            $args[0] = $transformer(...$args);

            return $args;
        }, $args)[0];
    }

    protected function getTransformer(): array
    {
        return $this->transformers;
    }
}
