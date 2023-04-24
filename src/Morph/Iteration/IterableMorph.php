<?php

declare(strict_types=1);

namespace Morph\Iteration;

interface IterableMorph
{
    public function __invoke(iterable $value, ...$args): iterable;
}
