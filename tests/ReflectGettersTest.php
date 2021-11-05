<?php

use Morph\Getters;
use Morph\Reflection\Method;
use Morph\Sequence;
use Morph\Tests\Fixtures\User;
use Morph\TransformValues;

test('ReflectGetters', function () {
    $userTransformer = new Sequence([
        new Getters(),
        new TransformValues(static fn (Method $property) => $property->getReturnTypeName()),
    ]);

    expect($userTransformer(User::class))->toBe([
        'Name' => 'string',
    ]);

    $userTransformer = new Sequence([
        new Getters(['is']),
        new TransformValues(static fn (Method $property) => $property->getReturnTypeName()),
    ]);

    expect($userTransformer(User::class))->toBe([
        'Safe' => 'bool',
    ]);
});
