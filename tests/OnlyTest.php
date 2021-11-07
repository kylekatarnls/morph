<?php

use Morph\Only;
use Morph\Sequence;
use Morph\TransformValues;

test('Only', function () {
    $description = [
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
    ];

    $select = new Sequence([
        new Only(['firstName', 'lastName']),
        new TransformValues(new Only('description')),
    ]);

    expect($select($description))->toBe([
        'firstName' => [
            'description' => "First name(s) / Surname(s).\n\nIncludes middle name(s).",
        ],
        'lastName' => [
            'description' => 'Last (family) name(s).',
        ],
    ]);

    $select = new Sequence([
        new Only('firstName'),
        new TransformValues(new Only(['description'])),
    ]);

    expect($select($description))->toBe([
        'firstName' => [
            'description' => "First name(s) / Surname(s).\n\nIncludes middle name(s).",
        ],
    ]);

    $select = new Only([]);

    expect($select($description))->toBe([]);
});
