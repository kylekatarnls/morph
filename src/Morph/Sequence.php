<?php

declare(strict_types=1);

namespace Morph;

class Sequence extends MorphBase
{
    protected array $transformers;
    protected array $transformersCache;

    public function __construct(array $transformers = [])
    {
        $this->transformers = $transformers;
    }

    public function __invoke(...$args)
    {
        $this->transformersCache ??= $this->getTransformers();

        return array_reduce($this->transformersCache, function (array $args, $transformer) {
            $args[0] = $this->useTransformerWith($transformer, ...$args);

            return $args;
        }, $args)[0];
    }

    protected function getTransformers(): array
    {
        return $this->transformers;
    }
}
