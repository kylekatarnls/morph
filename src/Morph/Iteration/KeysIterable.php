<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Generator;
use Morph\MorphBase;

class KeysIterable extends MorphBase implements IterableMorph
{
    public function __invoke(iterable $value, ...$args): array|Generator
    {
        return is_array($value)
            ? array_keys($value)
            : $this->getGeneratorKeys($value);
    }

    private function getGeneratorKeys(iterable $value): Generator
    {
        foreach ($value as $key => $ignored) {
            yield $key;
        }
    }
}
