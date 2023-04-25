<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class FlipIterable extends MorphBase implements IterableMorph
{
    public function __invoke(iterable $value, ...$args): iterable
    {
        if (is_array($value)) {
            return array_flip($value);
        }

        foreach ($value as $key => $item) {
            yield $item => $key;
        }
    }
}
