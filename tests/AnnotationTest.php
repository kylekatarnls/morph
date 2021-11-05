<?php

use Morph\Tests\Fixtures\User;
use Morph\Tests\Fixtures\UserDefiner;

test('Annotation', function () {
    require_once __DIR__ . '/Fixtures/UserDefiner.php';

    $userTransformer = new UserDefiner();

    expect($userTransformer->transform(User::class))->toBe(array_merge(
        version_compare(PHP_VERSION, '8.0.0-dev', '>=') ? [
            'id' => [
                'type' => 'int',
                'description' => '',
            ],
        ] : [],
        [
            'firstName' => [
                'type' => 'string',
                'description' => "First name(s) / Surname(s).\n\nIncludes middle name(s).",
            ],
        ],
    ));
});
