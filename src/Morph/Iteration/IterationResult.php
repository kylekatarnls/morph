<?php

declare(strict_types=1);

namespace Morph\Iteration;

interface IterationResult
{
    public function __invoke(iterable $value, ...$args): mixed;
}
