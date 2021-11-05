<?php

declare(strict_types=1);

namespace Morph;

class Merge extends MorphBase
{
    protected array $transformers;

    public function __construct(array $transformers = [])
    {
        $this->transformers = $transformers;
    }

    public function __invoke($value, ...$args): array
    {
        return array_merge(...array_map(
            static fn ($transformer) => $transformer($value, ...$args),
            $this->getTransformer(),
        ));
    }

    protected function getTransformer(): array
    {
        return $this->transformers;
    }
}
