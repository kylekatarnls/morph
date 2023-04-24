<?php

declare(strict_types=1);

namespace Morph\Iteration;

use InvalidArgumentException;
use Morph\MorphBase;

class FilterIterable extends MorphBase implements IterableMorph
{
    private $transformer;

    private bool $dropIndex;

    public function __construct(
        ?callable $transformer = null,
        ?string $key = null,
        ?string $property = null,
        bool $dropIndex = false
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
        $this->dropIndex = $dropIndex;
    }

    public function __invoke(iterable $value, ...$args): iterable
    {
       foreach ($value as $index => $item) {
           if (!($this->transformer)($item, $index, ...$args)) {
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
