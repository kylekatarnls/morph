<?php

declare(strict_types=1);

namespace Morph\Iteration;

use InvalidArgumentException;
use Morph\MorphBase;

class MapIterable extends MorphBase implements IterableMorph
{
    private $transformer;

    public function __construct(
        ?callable $transformer = null,
        ?string $key = null,
        ?string $property = null
    ) {
        if ($key !== null) {
            if ($transformer !== null) {
                throw new InvalidArgumentException(
                    'You can set only one of transformer, key or property'
                );
            }

            $transformer = static fn ($item) => $item[$key] ?? null;
        }

        if ($property !== null) {
            if ($transformer !== null) {
                throw new InvalidArgumentException(
                    'You can set only one of transformer, key or property'
                );
            }

            $transformer = static fn ($item) => $item->$key ?? null;
        }

        $this->transformer = $transformer;
    }

    public function __invoke(iterable $value, ...$args): iterable
    {
        foreach ($value as $index => $item) {
            yield $index => ($this->transformer)($item, $index, ...$args);
        }
    }
}
