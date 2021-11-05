<?php

use Morph\PublicPropertiesToArray;
use Morph\Sequence;
use Morph\Tests\Fixtures\User;
use Morph\TransformValues;

test('PublicPropertiesToArray', function () {
    $userTransformer = new PublicPropertiesToArray();
    $user = new User(1, 'Gladys', 'Horton', '123', 'jbx8{j*D8sqE');

    expect($userTransformer($user))->toBe([
        'id' => 1,
        'firstName' => 'Gladys',
        'lastName' => 'Horton',
    ]);

    $userTransformer = new Sequence([
        new PublicPropertiesToArray(),
        new TransformValues(static fn ($value) => is_string($value) ? strtoupper($value) : $value),
    ]);

    expect($userTransformer($user))->toBe([
        'id' => 1,
        'firstName' => 'GLADYS',
        'lastName' => 'HORTON',
    ]);
});
