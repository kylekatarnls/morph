<?php

use Morph\Only;
use Morph\Pick;
use Morph\Sequence;
use Morph\TransformKeys;
use Morph\TransformValues;

test('TransformKeys', function () {
    $data = [
        'first_name' => 'Ann',
        'last_name' => 'Bogan',
    ];

    $upperKeys = new TransformValues('mb_strtoupper');

    expect($upperKeys($data))->toBe([
        'first_name' => 'ANN',
        'last_name' => 'BOGAN',
    ]);
});
