<?php

declare(strict_types=1);

namespace Morph;

class LowerKeysFirstLetter extends TransformKeys
{
    private array $extraKeyMapping;

    public function __construct(array $extraKeyMapping = [])
    {
        parent::__construct(new LowerFirstLetter($extraKeyMapping));
    }
}
