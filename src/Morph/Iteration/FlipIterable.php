<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Generator;
use Morph\MorphBase;

class FlipIterable extends MorphBase implements IterableMorph
{
    public function __invoke(iterable $value, ...$args): array|Generator
    {
        return is_array($value)
            ? array_flip($value)
            : $this->flipGenerator($value);
    }

    private function flipGenerator(iterable $value): Generator
    {
        foreach ($value as $key => $item) {
            yield $item => $key;
        }
    }
}
