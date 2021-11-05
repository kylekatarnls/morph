<?php

declare(strict_types=1);

namespace Morph;

class UpperFirstLetter extends MorphBase
{
    private array $extraKeyMapping;

    public function __construct(array $extraKeyMapping = [])
    {
        $this->extraKeyMapping = $extraKeyMapping;
    }

    public function __invoke($value)
    {
        return is_string($value)
            ? ($this->extraKeyMapping[$value] ?? ucfirst($value))
            : $value;
    }
}
