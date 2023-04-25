<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class ValuesIterable extends MorphBase implements IterableMorph
{
    public function __invoke(iterable $value, ...$args): iterable
    {
        if (is_array($value)) {
            return array_values($value);
        }

        foreach ($value as $item) {
           yield $item;
       }
    }
}
