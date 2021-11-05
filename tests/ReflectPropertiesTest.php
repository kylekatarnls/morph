<?php

use Morph\Properties;
use Morph\Reflection\Property;
use Morph\Sequence;
use Morph\Tests\Fixtures\User;
use Morph\TransformValues;

test('Properties', function () {
    $userTransformer = new Sequence([
        new Properties(),
        new TransformValues(static fn (Property $property) => $property->getTypeName()),
    ]);

    expect($userTransformer(User::class))->toBe([
        'id' => 'int',
        'firstName' => 'string',
        'lastName' => 'string',
        'bankAccountNumber' => 'string',
        'password' => 'string',
    ]);
});
