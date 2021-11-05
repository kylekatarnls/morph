<?php

declare(strict_types=1);

namespace Morph;

class PublicPropertiesToArray extends MorphBase
{
    public function __invoke(object $value): array
    {
        return get_object_vars($value);
    }
}
