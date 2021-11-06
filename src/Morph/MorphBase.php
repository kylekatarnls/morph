<?php

declare(strict_types=1);

namespace Morph;

use InvalidArgumentException;

abstract class MorphBase implements Morph
{
    /**
     * Invoke current transformer to transform given value.
     */
    public function transform(...$args)
    {
        return $this->__invoke(...$args);
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
