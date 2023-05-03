<?php

declare(strict_types=1);

namespace Morph\Iteration;

use Morph\MorphBase;

class ReduceIterable extends MorphBase implements IterationResult
{
    private $callback;

    public function __construct(
        callable $callback,
        private mixed $initial = null,
    ) {
        $this->callback = $callback;
    }

    public function __invoke(iterable $value, mixed $initial = null, ...$args): mixed
    {
        $initial ??= $this->initial;

        return is_array($value)
            ? array_reduce(
                $value,
                $args === []
                    ? $this->callback
                    : static fn ($carry, $item) => ($this->callback)($carry, $item, ...$args),
                $initial,
            )
            : $this->reduceGenerator($value, $initial, ...$args);
    }

    private function reduceGenerator(iterable $value, mixed $carry = null, ...$args): mixed
    {
        foreach ($value as $item) {
            $carry = ($this->callback)($carry, $item, ...$args);
        }

        return $carry;
    }
}
