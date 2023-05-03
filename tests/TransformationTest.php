<?php

use Morph\Transformation;

test('Transformation', function () {
    $gen = static function (): iterable {
        for ($i = 1; $i <= 6; $i++) {
            yield $i;
        }
    };

    expect(
        Transformation::take($gen())
            ->filter(static fn (int $number) => $number !== 4)
            ->map(static fn (int $number) => [
                'number' => $number,
                'odd' => $number % 2 === 0,
            ])
            ->filter(key: 'odd')
            ->values()
            ->array()
            ->get(),
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

    expect(
        Transformation::take(42)->get(),
    )->toBe(
        42,
    );

    expect(
        Transformation::take('abc')->then('strtoupper')->get(),
    )->toBe(
        'ABC',
    );

    expect(
        Transformation::take([1, 2, 3])->reverse()->get(),
    )->toBe(
        [3, 2, 1],
    );

    expect(
        Transformation::take([1, 2, 3])->first()->get(),
    )->toBe(
        1,
    );

    expect(
        Transformation::take([1, 2, 3])->last()->get(),
    )->toBe(
        3,
    );
});
