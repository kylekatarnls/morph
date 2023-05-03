<?php

use Morph\Tests\Fixtures\User;
use Morph\Tests\Fixtures\UserDefiner;

test('Transformation', function () {
    $gen = static function (): iterable {
        for ($i = 1; $i <= 6; $i++) {
            yield $i;
        }
    };

    expect(
        \Morph\Transformation::take($gen())
            ->filter(static fn (int $number) => $number !== 4)
            ->map(static fn (int $number) => [
                'number' => $number,
                'odd' => $number % 2 === 0,
            ])
            ->filter(key: 'odd')
            ->values()
            ->array()
            ->get()
    )->toBe(
        [
            [
                'number' => 2,
                'odd' => true,
            ],
            [
                'number' => 6,
                'odd' => true,
            ],
        ],
    );
});
