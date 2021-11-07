<?php

use Morph\GettersToArray;
use Morph\LowerKeysFirstLetter;
use Morph\Merge;
use Morph\PublicPropertiesToArray;
use Morph\Sequence;
use Morph\Tests\Fixtures\User;
use Morph\UpperKeysFirstLetter;

test('Merge', function () {
    $merge = new Merge([
        static fn ($value) => ['items' => $value],
        static fn ($value) => ['total' => count($value)],
    ]);

    expect($merge(['A', 'B']))->toBe([
        'items' => ['A', 'B'],
        'total' => 2,
    ]);

    $userTransformer = new Merge([
        new PublicPropertiesToArray(),
        new Sequence([new GettersToArray(['get', 'is']), new LowerKeysFirstLetter(['ID' => 'id'])]),
    ]);
    $user = new User(2, 'Wanda', 'Young', '456', 'JKH2G2563gsfDR1');
    $info = $userTransformer($user);

    expect($info)->toBe([
        'id' => 2,
        'firstName' => 'Wanda',
        'lastName' => 'Young',
        'name' => 'Wanda Young',
        'safe' => true,
    ]);

    $infoTransformer = new Merge([
        new UpperKeysFirstLetter(['ID' => 'id']),
        static fn (array $value) => ['Capitals' => $value['firstName'][0] . $value['lastName'][0]],
        static fn () => ['Static' => 'foobar'],
    ]);

    expect($infoTransformer->transform($info))->toBe([
        'Id' => 2,
        'FirstName' => 'Wanda',
        'LastName' => 'Young',
        'Name' => 'Wanda Young',
        'Safe' => true,
        'Capitals' => 'WY',
        'Static' => 'foobar',
    ]);
});
