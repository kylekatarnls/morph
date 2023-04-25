<?php

declare(strict_types=1);

namespace Morph;

use Closure;

final class Transformation
{
    private readonly Morph|Closure|string|array|null $transformers;

    public function __construct(
        private readonly mixed $value,
        private readonly array $args = [],
        Morph|callable|null $transformers = null,
    ) {
        $this->transformers = $transformers;
    }

    public static function take(mixed $value, ...$args): self
    {
        return new self($value, $args);
    }

    public function get(): mixed
    {
        if ($this->transformers === null) {
            return $this->value;
        }

        if ($this->transformers instanceof Morph) {
            return $this->transformers->transform($this->value, ...$this->args);
        }

        return ($this->transformers)($this->value, ...$this->args);
    }

    public function concat(array|Sequence $transformers): self
    {
        return new self($this->value, $this->args, $this->getSequence()->concat($transformers));
    }

    public function then(callable|Morph $transformer): self
    {
        return new self($this->value, $this->args, $this->getSequence()->then($transformer));
    }

    public function pick(string|int $key): self
    {
        return new self($this->value, $this->args, $this->getSequence()->pick($key));
    }

    public function only($keys): self
    {
        return new self($this->value, $this->args, $this->getSequence()->only($keys));
    }

    public function filter(
        ?callable $transformer = null,
        ?string $key = null,
        ?string $property = null,
        bool $dropIndex = false
    ): self {
        return new self($this->value, $this->args, $this->getSequence()->filter(
            $transformer,
            $key,
            $property,
            $dropIndex
        ));
    }

    public function map(
        ?callable $transformer = null,
        ?string $key = null,
        ?string $property = null
    ): self {
        return new self($this->value, $this->args, $this->getSequence()->map(
            $transformer,
            $key,
            $property
        ));
    }

    public function array(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->array());
    }

    public function first(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->first());
    }

    public function reverse(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->reverse());
    }

    public function last(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->last());
    }

    public function keys(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->keys());
    }

    public function values(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->values());
    }

    public function count(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->count());
    }

    public function sum(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->sum());
    }

    public function reduce(callable $callback): self
    {
        return new self($this->value, $this->args, $this->getSequence()->reduce($callback));
    }

    public function flip(): self
    {
        return new self($this->value, $this->args, $this->getSequence()->flip());
    }

    protected function getSequence(): Sequence
    {
        if ($this->transformers === null) {
            return new Sequence();
        }

        if ($this->transformers instanceof Sequence) {
            return $this->transformers;
        }

        return new Sequence([$this->transformers]);
    }
}
