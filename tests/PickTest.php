<?php

use Morph\Only;
use Morph\Pick;
use Morph\Sequence;
use Morph\TransformValues;

test('Pick', function () {
    $description = [
        'id' => [
            'type' => 'int',
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
        new TransformValues(new Pick('description')),
    ]);

    expect($select($description))->toBe([
        'id' => null,
        'firstName' => "First name(s) / Surname(s).\n\nIncludes middle name(s).",
        'lastName' => 'Last (family) name(s).',
    ]);

    $select = new Sequence([
        new Pick('firstName'),
    ]);

    expect($select($description))->toBe([
        'type' => 'string',
        'description' => "First name(s) / Surname(s).\n\nIncludes middle name(s).",
    ]);

    $select = new Sequence([
        new TransformValues(new Pick('description')),
        new Pick('firstName'),
    ]);

    expect($select($description))->toBe(
        "First name(s) / Surname(s).\n\nIncludes middle name(s).",
    );

    $select = new Sequence([
        new Pick('firstName'),
        new Pick('description'),
    ]);

    expect($select($description))->toBe(
        "First name(s) / Surname(s).\n\nIncludes middle name(s).",
    );
});
