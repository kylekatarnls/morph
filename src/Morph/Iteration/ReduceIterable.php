<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class ReduceIterable extends MorphBase implements IterationResult
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(iterable $value, mixed $initial = null, ...$args): iterable
    {
        if (is_array($value)) {
            return array_reduce(
                $value,
                $args === []
                    ? $this->callback
                    : static fn ($carry, $item) => ($this->callback)($carry, $item, ...$args),
                $initial,
            );
        }

        $carry = $initial;

        foreach ($value as $item) {
            $carry = ($this->callback)($carry, $item, ...$args);
        }

        return $carry;
    }
}
