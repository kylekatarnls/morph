<?php

declare(strict_types=1);

namespace Morph;

abstract class MorphBase implements Morph
{
    /**
     * Invoke current transformer to transform given value.
     */
    public function transform(...$args)
    {
        return $this->__invoke(...$args);
    }
}
