<?php

declare(strict_types=1);

namespace Morph;

class UpperKeysFirstLetter extends TransformKeys
{
    private array $extraKeyMapping;

    public function __construct(array $extraKeyMapping = [])
    {
        parent::__construct(new UpperFirstLetter($extraKeyMapping));
    }
}
