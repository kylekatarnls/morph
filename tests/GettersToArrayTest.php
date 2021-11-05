<?php

use Morph\GettersToArray;
use Morph\Tests\Fixtures\User;

test('GettersToArray', function () {
    $userTransformer = new GettersToArray();
    $user = new User(1, 'Gladys', 'Horton', '123', 'jbx8{j*D8sqE');

    expect($userTransformer($user))->toBe([
        'Name' => 'Gladys Horton',
    ]);

    $userTransformer = new GettersToArray(['get', 'is']);
    $user = new User(1, 'Gladys', 'Horton', '123', 'jbx8{j*D8sqE');

    expect($userTransformer($user))->toBe([
        'Name' => 'Gladys Horton',
        'Safe' => true,
    ]);
});
