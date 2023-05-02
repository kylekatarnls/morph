<?php

use Morph\Tests\Fixtures\ModelTransformer;

test('Readme', function () {
    $user = new class () {
        public int $id;
        public string $name;
        public string $email;
        public string $label;
    };
    $user->id = 42;
    $user->name = 'Katherine Anderson';
    $user->email = 'katherine.anderson@marvelettes.org';
    $user->label = '';

    $transformer = new Morph\Sequence([
        new Morph\PublicPropertiesToArray(),
        'array_filter',
        new Morph\UpperKeysFirstLetter(['id' => 'ID']),
    ]);

    $info = $transformer->transform($user);

    expect($info)->toBe([
        'ID' => 42,
        'Name' => 'Katherine Anderson',
        'Email' => 'katherine.anderson@marvelettes.org',
    ]);

    $transformer = new Morph\Merge([
        static fn ($value) => ['username' => $value->name],
        new class () implements Morph\Morph {
            public function transform($value = null, $hashAlgo = null): array
            {
                if ($value && $hashAlgo) {
                    return ['userid' => hash((string) $hashAlgo, (string) $value->id)];
                }

                return [];
            }
        },
    ]);

    $info = $transformer->transform($user, 'sha1');

    expect($info)->toBe([
        'username' => 'Katherine Anderson',
        'userid' => '92cfceb39d57d914ed8b14d0e37643de0797ae56',
    ]);

    $transformer = new class ('sha1') extends Morph\Merge
    {
        private string $idHashAlgo;

        public function __construct(string $idHashAlgo)
        {
            $this->idHashAlgo = $idHashAlgo;
        }

        protected function getTransformers(): array
        {
            return [
                static fn($value) => ['username' => $value->name],
                new class ($this->idHashAlgo) extends Morph\MorphBase {
                    private string $hashAlgo;

                    public function __construct(string $hashAlgo)
                    {
                        $this->hashAlgo = $hashAlgo;
                    }

                    public function __invoke($value = null): array
                    {
                        if ($value) {
                            return ['userid' => hash($this->hashAlgo, (string) $value->id)];
                        }

                        return [];
                    }
                },
            ];
        }
    };

    expect($info)->toBe([
        'username' => 'Katherine Anderson',
        'userid' => '92cfceb39d57d914ed8b14d0e37643de0797ae56',
    ]);

    $transformer = new Morph\Sequence([
        new Morph\PublicPropertiesToArray(),
        new stdClass(),
        new Morph\UpperKeysFirstLetter(['id' => 'ID']),
    ]);

    expect(static fn () => $transformer->transform($user))->toThrow(
        InvalidArgumentException::class,
        implode(' ', [
            'Invalid transformer passed.',
            'A transformer must be callable or implement a transform() method.',
            'Given value type: stdClass.',
        ]),
    );

    $transformer = new Morph\Sequence([
        12,
        new Morph\PublicPropertiesToArray(),
        new Morph\UpperKeysFirstLetter(['id' => 'ID']),
    ]);

    expect(static fn () => $transformer->transform($user))->toThrow(
        InvalidArgumentException::class,
        implode(' ', [
            'Invalid transformer passed.',
            'A transformer must be callable or implement a transform() method.',
            'Given value type: integer.',
        ]),
    );
})->group('readme');

test('JsonSerializable', function () {
    require_once __DIR__ . '/Fixtures/ModelTransformer.php';

    $user = new class() implements JsonSerializable
    {
        public int $id;
        public string $name;
        public string $email;
        public string $label;

        public function jsonSerialize(): array
        {
            return ModelTransformer::get()->transform($this);
        }
    };
    $user->id = 42;
    $user->name = 'Katherine Anderson';
    $user->email = 'katherine.anderson@marvelettes.org';
    $user->label = '';

    expect(json_encode($user, JSON_PRETTY_PRINT))->toBe(str_replace("\r", '', <<<OES
        {
            "ID": 42,
            "Name": "Katherine Anderson",
            "Email": "katherine.anderson@marvelettes.org"
        }
        OES));
})->group('readme');

test('Everything is documented', function () {
    preg_match_all(
        '/^###?\s+(?<title>\S.*\S)\s*$/m',
        file_get_contents(__DIR__ . '/../README.md'),
        $matches,
    );
    $classes = [
        ...array_filter(
            array_map(
                static fn (string $file) => pathinfo($file, PATHINFO_FILENAME),
                glob(__DIR__ . '/../src/Morph/*.php'),
            ),
            static fn (string $class) => class_exists('Morph\\' . $class),
        ),
        ...array_filter(
            array_map(
                static fn (string $file) => pathinfo($file, PATHINFO_FILENAME),
                glob(__DIR__ . '/../src/Morph/Iteration/*.php'),
            ),
            static fn (string $class) => class_exists('Morph\\Iteration\\' . $class),
        ),
    ];

    expect(array_diff($classes, $matches['title']))->toBe([]);
})->group('readme');
