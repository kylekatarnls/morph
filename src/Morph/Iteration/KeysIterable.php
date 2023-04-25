<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class KeysIterable extends MorphBase implements IterableMorph
{
    public function __invoke(iterable $value, ...$args): iterable
    {
        if (is_array($value)) {
            return array_keys($value);
        }

        foreach ($value as $key => $ignored) {
            yield $key;
        }
    }
}
