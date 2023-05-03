<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class SumIterable extends MorphBase implements IterationResult
{
    public function __invoke(iterable $value, ...$args): int|float
    {
        if (is_array($value)) {
            return array_sum($value);
        }

        $sum = 0;

        foreach ($value as $item) {
            $sum += $item;
        }

        return $sum;
    }
}
