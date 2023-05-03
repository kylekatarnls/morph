<?php

use Morph\Iteration\MapIterable;
use Morph\Only;
use Morph\Pick;
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
        Transformation::take($gen())->reverse()->get(),
    )->toBe(
        [6, 5, 4, 3, 2, 1],
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
    )
    ->and(
        Transformation::take([])->first()->get(),
    )->toBe(
        null,
    );

    expect(
        Transformation::take([1, 2, 3])->last()->get(),
    )->toBe(
        3,
    );

    $newGen = Transformation::take($gen())->flip()->get();
    expect($newGen)->toBeInstanceOf(Generator::class);
    expect(
        iterator_to_array($newGen),
    )->toBe(
        [
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 3,
            5 => 4,
            6 => 5,
        ],
    );

    expect(
        Transformation::take(['a' => 1, 'b' => 2, 'x' => 3])->values()->get(),
    )->toBe(
        [1, 2, 3],
    );

    expect(
        Transformation::take([1, 2, 'x' => 3])->flip()->get(),
    )->toBe(
        [1 => 0, 2 => 1, 3 => 'x'],
    );

    $newGen = Transformation::take($gen())->keys()->get();
    expect($newGen)->toBeInstanceOf(Generator::class);
    expect(
        iterator_to_array($newGen),
    )->toBe(
        [0, 1, 2, 3, 4, 5],
    );

    expect(
        Transformation::take([1, 2, 'x' => 3])->keys()->get(),
    )->toBe(
        [0, 1, 'x'],
    );

    expect(
        Transformation::take([2, 4, 6])->count()->get(),
    )->toBe(
        3,
    );

    expect(
        Transformation::take($gen())->count()->get(),
    )->toBe(
        6,
    );

    expect(
        Transformation::take([2, 4, 6])->sum()->get(),
    )->toBe(
        12,
    );

    expect(
        Transformation::take($gen())->sum()->get(),
    )->toBe(
        21,
    );

    expect(
        Transformation::take(['a', 'b', 'c'])->reduce(
            static fn (string $carry, string $letter) => "$carry.$letter",
            '_',
        )->get(),
    )->toBe(
        '_.a.b.c',
    );

    expect(
        Transformation::take($gen())->reduce(
            static fn (int $previous, int $next) => $next * $previous,
            1,
        )->get(),
    )->toBe(
        720,
    );

    expect(
        Transformation::take([[0, 1], [2, 3], [4, 5]])->pick(1)->get(),
    )->toBe(
        [2, 3],
    );

    expect(
        Transformation::take([
            'type' => 'a',
            'active' => true,
            'name' => 'A',
        ])->pick('active')->get(),
    )->toBe(
        true,
    );
    expect(
        Transformation::take([
            'type' => 'a',
            'active' => true,
            'name' => 'A',
        ])->only(['name', 'type'])->get(),
    )->toBe(
        [
            'type' => 'a',
            'name' => 'A',
        ],
    );

    expect(
        Transformation::take([[0, 1], [2, 3], [4, 5]])->map(new Pick(1))->array()->get(),
    )->toBe(
        [1, 3, 5],
    );

    expect(
        Transformation::take([
            ['active' => true],
            ['active' => false],
            ['active' => true],
        ])->map(new Pick('active'))->array()->get(),
    )->toBe(
        [true, false, true],
    );

    expect(
        (new MapIterable((new Pick('active'))->concat([
            'intval',
            static fn (int $number) => $number * 2,
        ])))->then('iterator_to_array')([
            ['active' => true],
            ['active' => false],
            ['active' => true],
        ]),
    )->toBe(
        [2, 0, 2],
    );

    expect(
        Transformation::take([
            ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->map(new Only(['name', 'type']))->array()->get(),
    )->toBe(
        [
            ['type' => 'a', 'name' => 'A'],
            ['type' => 'b', 'name' => 'B'],
            ['type' => 'c', 'name' => 'C'],
        ],
    );

    expect(
        Transformation::take([
            ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->filter(key: 'active')->array()->get(),
    )->toBe(
        [
            0 => ['type' => 'a', 'active' => true, 'name' => 'A'],
            2 => ['type' => 'c', 'active' => true, 'name' => 'C'],
        ],
    );

    expect(
        Transformation::take([
            ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->filter(key: 'active', dropIndex: true)->array()->get(),
    )->toBe(
        [
            ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ],
    );

    expect(
        Transformation::take([
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            (object) ['type' => 'b', 'active' => false, 'name' => 'B'],
            (object) ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->filter(property: 'active', dropIndex: true)->array()->get(),
    )->toEqual(
        [
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            (object) ['type' => 'c', 'active' => true, 'name' => 'C'],
        ],
    );

    expect(
        Transformation::take([
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            (object) ['type' => 'b', 'active' => false, 'name' => 'B'],
            (object) ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->map(property: 'name')->array()->get(),
    )->toEqual(
        ['A', 'B', 'C'],
    );

    expect(
        Transformation::take([
            ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->map(key: 'name')->array()->get(),
    )->toEqual(
        ['A', 'B', 'C'],
    );

    $transformation = new Transformation(
        42,
        [6, 3],
        static fn (int $value, int $plus, int $minus) => $value + $plus - $minus,
    );

    expect($transformation->get())->toBe(45);

    $other = $transformation->concat([
        static fn (int $value, int $plus, int $minus) => $value + $plus,
        static fn (int $value, int $plus, int $minus) => $value - $minus,
    ]);

    expect($transformation->get())->toBe(45);
    expect($other->get())->toBe(48);

    expect(static function () {
        Transformation::take([
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->filter(key: 'active', property: 'active');
    })->toThrow(
        InvalidArgumentException::class,
        'You can set only one of transformer, key or property',
    );

    expect(static function () {
        Transformation::take([
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->filter('is_array', 'active');
    })->toThrow(
        InvalidArgumentException::class,
        'You can set only one of transformer, key or property',
    );

    expect(static function () {
        Transformation::take([
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->map(key: 'active', property: 'active');
    })->toThrow(
        InvalidArgumentException::class,
        'You can set only one of transformer, key or property',
    );

    expect(static function () {
        Transformation::take([
            (object) ['type' => 'a', 'active' => true, 'name' => 'A'],
            ['type' => 'b', 'active' => false, 'name' => 'B'],
            ['type' => 'c', 'active' => true, 'name' => 'C'],
        ])->map('is_array', 'active');
    })->toThrow(
        InvalidArgumentException::class,
        'You can set only one of transformer, key or property',
    );
});
