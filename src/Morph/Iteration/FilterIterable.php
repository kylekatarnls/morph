<?php

declare(strict_types=1);

namespace Morph\Iteration;

use InvalidArgumentException;
use Morph\MorphBase;

class FilterIterable extends MorphBase implements IterableMorph
{
    private $callback;

    private bool $dropIndex;

    public function __construct(
        ?callable $callback = null,
        ?string $key = null,
        ?string $property = null,
        bool $dropIndex = false
    ) {
        if ($key !== null) {
            if ($callback !== null) {
                throw new InvalidArgumentException(
                    'You can set only one of transformer, key or property'
                );
            }

            $callback = static fn ($item) => $item[$key] ?? null;
        }

        if ($property !== null) {
            if ($callback !== null) {
                throw new InvalidArgumentException(
                    'You can set only one of transformer, key or property'
                );
            }

            $callback = static fn ($item) => $item->$key ?? null;
        }

        $this->callback = $callback;
        $this->dropIndex = $dropIndex;
    }

    public function __invoke(iterable $value, ...$args): iterable
    {
        foreach ($value as $index => $item) {
            if (!($this->callback)($item, $index, ...$args)) {
                continue;
            }

            if ($this->dropIndex) {
                yield $item;

                continue;
            }

            yield $index => $item;
        }
    }
}
