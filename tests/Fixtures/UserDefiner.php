<?php

declare(strict_types=1);

namespace Morph\Tests\Fixtures;

use Morph\FilterValues;
use Morph\Getters;
use Morph\LowerKeysFirstLetter;
use Morph\Merge;
use Morph\PublicProperties;
use Morph\Reflection\Documented;
use Morph\Sequence;
use Morph\TransformValues;

final class UserDefiner extends Sequence
{
    protected function getTransformers(): array
    {
        return [
            new Merge([
                new PublicProperties(),
                new Getters(['get', 'is']),
            ]),
            new LowerKeysFirstLetter(),
            new FilterValues(static fn (Documented $property) => $property->hasAttributeOrIsAnnotatedWith(
                Exposed::class,
            )),
            new TransformValues(static fn (Documented $property) => [
                'type' => $property->getTypeName(),
                'description' => $property->getDescription(),
            ]),
        ];
    }
}
