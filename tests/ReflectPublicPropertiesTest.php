<?php

use Morph\PublicProperties;
use Morph\Reflection\Property;
use Morph\Sequence;
use Morph\Tests\Fixtures\User;
use Morph\TransformValues;

test('PublicProperties', function () {
    $userTransformer = new Sequence([
        new PublicProperties(),
        new TransformValues(static fn (Property $property) => [
            'type' => $property->getTypeName(),
            'description' => $property->getDescription(),
        ]),
    ]);

    expect($userTransformer(User::class))->toBe([
        'id' => [
            'type' => 'int',
            'description' => '',
        ],
        'firstName' => [
            'type' => 'string',
            'description' => "First name(s) / Surname(s).\n\nIncludes middle name(s).",
        ],
        'lastName' => [
            'type' => 'string',
            'description' => 'Last (family) name(s).',
        ],
    ]);
});
