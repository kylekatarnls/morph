<?php

use Morph\FilterKeys;

test('FilterKeys', function () {
    $description = [
        'id' => 1,
        'firstName' => 2,
        'lastName' => 3,
    ];

    $select = new FilterKeys(static fn (string $key) => substr($key, -4) === 'Name');

    expect($select($description))->toBe([
        'firstName' => 2,
        'lastName' => 3,
    ]);
});
