<?php

declare(strict_types=1);

namespace Morph;

class Merge extends MorphBase
{
    protected array $transformers;
    protected array $transformersCache;

    public function __construct(array $transformers = [])
    {
        $this->transformers = $transformers;
    }

    public function __invoke($value, ...$args): array
    {
        $this->transformersCache ??= $this->getTransformers();

        return array_merge(...array_map(
            fn ($transformer) => $this->useTransformerWith($transformer, $value, ...$args),
            $this->transformersCache,
        ));
    }

    protected function getTransformers(): array
    {
        return $this->transformers;
    }
}
