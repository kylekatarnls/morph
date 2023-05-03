<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Generator;
use Morph\MorphBase;

class ValuesIterable extends MorphBase implements IterableMorph
{
    public function __invoke(iterable $value, ...$args): array|Generator
    {
        return is_array($value)
            ? array_values($value)
            : $this->getGeneratorValues($value);
    }

    private function getGeneratorValues(iterable $value): Generator
    {
        foreach ($value as $item) {
            yield $item;
        }
    }
}
