<?php

declare(strict_types=1);

namespace Morph;

use InvalidArgumentException;
use Morph\Iteration\CountIterable;
use Morph\Iteration\FilterIterable;
use Morph\Iteration\FlipIterable;
use Morph\Iteration\KeysIterable;
use Morph\Iteration\MapIterable;
use Morph\Iteration\ReduceIterable;
use Morph\Iteration\SumIterable;
use Morph\Iteration\ValuesIterable;
use Traversable;

abstract class MorphBase implements Morph
{
    /**
     * Invoke current transformer to transform given value.
     */
    public function transform(...$args)
    {
        return $this->__invoke(...$args);
    }

    public function concat(array|Sequence $transformers): Sequence
    {
        return new Sequence([
            $this,
            ...(is_array($transformers)
                ? $transformers
                : $transformers->getTransformers()
            ),
        ]);
    }

    public function then(callable|Morph $transformer): Sequence
    {
        return $this->concat([$transformer]);
    }

    public function pick(string|int $key): Sequence
    {
        return $this->then(new Pick($key));
    }

    public function only($keys): Sequence
    {
        return $this->then(new Only($keys));
    }

    public function filter(
        ?callable $transformer = null,
        ?string $key = null,
        ?string $property = null,
        bool $dropIndex = false
    ): Sequence {
        return $this->then(new FilterIterable($transformer, $key, $property, $dropIndex));
    }

    public function map(
        ?callable $transformer = null,
        ?string $key = null,
        ?string $property = null
    ): Sequence {
        return $this->then(new MapIterable($transformer, $key, $property));
    }

    public function array(): Sequence
    {
        return $this->then(static fn ($value) => $value instanceof Traversable
            ? iterator_to_array($value)
            : (array) $value
        );
    }

    public function first(): Sequence
    {
        return $this->then(static function (iterable $value) {
            foreach ($value as $item) {
                return $item;
            }

            return null;
        });
    }

    public function reverse(): Sequence
    {
        return $this->array()->then(array_reverse(...));
    }

    public function last(): Sequence
    {
        return $this->reverse()->first();
    }

    public function keys(): Sequence
    {
        return $this->then(new KeysIterable());
    }

    public function values(): Sequence
    {
        return $this->then(new ValuesIterable());
    }

    public function count(): Sequence
    {
        return $this->then(new CountIterable());
    }

    public function sum(): Sequence
    {
        return $this->then(new SumIterable());
    }

    public function reduce(callable $callback): Sequence
    {
        return $this->then(new ReduceIterable($callback));
    }

    public function flip(): Sequence
    {
        return $this->then(new FlipIterable());
    }

    protected function useTransformerWith($transformer, ...$args)
    {
        if (is_callable($transformer)) {
            return $transformer(...$args);
        }

        if (is_object($transformer) && method_exists($transformer, 'transform')) {
            return $transformer->transform(...$args);
        }

        throw new InvalidArgumentException(implode(' ', [
            'Invalid transformer passed.',
            'A transformer must be callable or implement a transform() method.',
            'Given value type: ' . (is_object($transformer) ? get_class($transformer) : gettype($transformer)) . '.',
        ]));
    }

    protected function mapWithTransformer($transformer, ...$args): array
    {
        return array_map(
            fn (...$values) => $this->useTransformerWith($transformer, ...$values),
            ...$args,
        );
    }
}
