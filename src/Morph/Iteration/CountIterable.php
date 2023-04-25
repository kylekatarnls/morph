<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class CountIterable extends MorphBase implements IterationResult
{
    public function __invoke(iterable $value, ...$args): int
    {
        if (is_countable($value)) {
            return count($value);
        }

        $counter = 0;

        foreach ($value as $ignored) {
            $counter++;
        }

        return $counter;
    }
}
