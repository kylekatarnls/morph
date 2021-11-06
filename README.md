# Morph

Generic tooling to compose transformations

```php
class User
{
    public int $id;
    public string $name;
    public string $email;
    public string $label;
}

$user = new User();
$user->id = 42;
$user->name = 'Katherine Anderson';
$user->email = 'katherine.anderson@marvelettes.org';
$user->label = '';

$transformer = new Morph\Sequence([
    new Morph\PublicPropertiesToArray(),
    'array_filter', // or static fn (array $value) => array_filter($value),
    new Morph\UpperKeysFirstLetter(['id' => 'ID']),
    // a transformer can be composed with any kind of callable
]);

$info = $transformer->transform($user);
// or simply:
$info = $transformer($user);
```
`$info` is an array as follows:
```
[
    'ID' => 42,
    'Name' => 'Katherine Anderson',
    'Email' => 'katherine.anderson@marvelettes.org',
]
```

## Installation

```
composer require kylekatarnls/morph
```

## Usage

All the classes under the `Morph` namespace implements `Morph\Morph`:
```php
interface Morph
{
    public function transform();
}
```

And are also `callable`.

Any kind of `callable` (`Closure`, function name, invokable objects)
or instance of a class that implements `Morph\Morph` can be used as
a transformation (e.g. be used in a `Morph\Sequence`, `Morph\Merge`
etc.)

```php
class HashUserId implements Morph\Morph
{
    public function transform($value = null, $hashAlgo = null): array
    {
        if ($value && $hashAlgo) {
            return ['userid' => hash((string) $hashAlgo, (string) $value->id)];
        }

        return [];
    }
}

$transformer = new Morph\Merge([
    static fn ($value) => ['username' => $value->name],
    new class HashUserId(),
]);

$user = (object) ['id' => 42, 'name' => 'Georgeanna Tillman'];

var_dump($transformer->transform($user, 'sha1'));
/* [
    'username' => 'Georgeanna Tillman',
    'userid' => '92cfceb39d57d914ed8b14d0e37643de0797ae56',
] */
```

The transformer above can also be written in a class:

```php
class UserTransformer extends Morph\Merge
{
    protected function getTransformers(): array
    {
        return [
            static fn ($value) => ['username' => $value->name],
            new class HashUserId(),
        ];
    }
}

$transformer = new UserTransformer();

var_dump($transformer->transform($user, 'sha1'));
```

The `Morph\Sequence` can be written the same way overriding the
`getTransformers` method.

Note that this syntax allows to lazy-load the inner transformers that
won't even be created if `new UserTransformer` is not called. And they
will still be cached once created so if you call `->transform()`
multiple times, the same transformers instances are re-used.

In the example above `'sha1'` is passed as an additional parameter
of `transform()` and is passed to each sub-transformer while in this
case, it's only used by `HashUserId`, another option is to create
a static config for `HashUserId` and `UserTransformer`:

```php
class HashUserId extends Morph\MorphBase
{
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
}

class UserTransformer extends Morph\Merge
{
    private string $idHashAlgo;

    public function __construct(string $idHashAlgo)
    {
        $this->idHashAlgo = $idHashAlgo;
    }

    protected function getTransformers(): array
    {
        return [
            static fn ($value) => ['username' => $value->name],
            new class HashUserId($this->idHashAlgo),
        ];
    }
}

$transformer = new UserTransformer('sha1');

var_dump($transformer->transform($user));
```

```php
class User implements JsonSerializable
{
    public int $id;
    public string $name;
    public string $email;
    public string $label;

    public function jsonSerialize(): array
    {
        return ModelTransformer::get()->transform($this);
    }
}

class ModelTransformer extends Morph\Sequence
{
    private static self $singleton;

    public static function get(): self
    {
        // We can cache the transformer instance
        // for better performances.
        // This can be done via a simple singleton
        // as below.
        // Or using a container (see Psr\Container\ContainerInterface)
        // such as the Symfony container.
        return self::$singleton ??= new self();
    }

    protected function getTransformers(): array
    {
        return [
            new Morph\PublicPropertiesToArray(),
            'array_filter',
            new Morph\UpperKeysFirstLetter(['id' => 'ID']),
        ];
    }
}

$user = new User();
$user->id = 42;
$user->name = 'Katherine Anderson';
$user->email = 'katherine.anderson@marvelettes.org';
$user->label = '';

echo json_encode($user, JSON_PRETTY_PRINT);
```
Output:
```json
{
    "ID": 42,
    "Name": "Katherine Anderson",
    "Email": "katherine.anderson@marvelettes.org"
}
```

## Why/when to use?

While it may be overkill to use `Morph` if you only use
`PublicPropertiesToArray` or just few simple transformations,
it becomes handy when you have complex Models or
[DTO](https://en.wikipedia.org/wiki/Data_transfer_object)
and want to properly isolate the handling of input and output
transformations, lazy-load them and/or share part of it among
the code base.

It provides a clean way to represent steps a transformations
process or
[ETL](https://en.wikipedia.org/wiki/Extract,_transform,_load)
system.

Last, `Morph` comes with `Reflection` tooling which can allow
to define a transformation right into the class definition,
using attributes or PHPDoc and so synchronize a class with
its definition and transformation. Typically, when using
an auto-documented API system such as
[GraphQL](https://graphql.org/) or
[Protobuf](https://developers.google.com/protocol-buffers).

See more in the [Reflection chapter](#Reflection).

## Reflection

```php
class ModelDefiner extends \Morph\Sequence
{
    protected function getTransformers(): array
    {
        return [
            new \Morph\Merge([
                new \Morph\PublicProperties(),
                new \Morph\Getters(['get', 'is']),
            ]),
            new \Morph\LowerKeysFirstLetter(),
            new \Morph\TransformValues(static fn (\Morph\Reflection\Documented $property) => array_filter([
                'type' => $property->getTypeName(),
                'description' => $property->getDescription(),
            ])),
        ];
    }
}

class User
{
    public int $id;

    /**
     * First name(s) / Surname(s).
     *
     * Includes middle name(s).
     */
    public string $firstName;

    /**
     * Last (family) name(s).
     */
    public string $lastName;

    /**
     * Bank account number.
     */
    protected string $bankAccountNumber;

    /**
     * Login password.
     */
    private string $password;

    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $bankAccountNumber,
        string $password
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->bankAccountNumber = $bankAccountNumber;
        $this->password = $password;
    }

    /**
     * Complete first and last name.
     */
    public function getName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isSafe(): bool
    {
        return strlen($this->password) >= 8;
    }
}

echo json_encode((new ModelDefiner())(User::class), JSON_PRETTY_PRINT);
```

Output:
```json
{
    "id": {
        "type": "int"
    },
    "firstName": {
        "type": "string",
        "description": "First name(s) \/ Surname(s).\n\nIncludes middle name(s)."
    },
    "lastName": {
        "type": "string",
        "description": "Last (family) name(s)."
    },
    "name": {
        "type": "string",
        "description": "Complete first and last name."
    },
    "safe": {
        "type": "bool"
    }
}
```
