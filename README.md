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
    new HashUserId(),
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
            new HashUserId(),
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
            new HashUserId($this->idHashAlgo),
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

## Built-in transformers

### FilterKeys

Filter an array to keep only keys for which the given callable
returns `true` (or is truthy if no callable was passed in the constructor).

```php
$removePrivateKeys = new \Morph\FilterKeys(
    static fn (string $key) => $key[0] !== '_',
);
$removePrivateKeys([
    'foo' => 'A',
    '_bar' => 'B',
    'biz' => 'C',
]);
```

```php
[
    'foo' => 'A',
    'biz' => 'C',
]
```

### FilterValues

Filter an array to keep only values for which the given callable
returns `true` (or is truthy if no callable was passed in the constructor).

```php
$removeLowValues = new \Morph\FilterValues(
    static fn ($value) => $value > 10,
);
$removeLowValues([
    'foo' => 12,
    '_bar' => 14,
    'biz' => 7,
]);
```

```php
[
    'foo' => 12,
    '_bar' => 14,
]
```

### Getters

Return the list of the methods (as an array of `\Morph\Reflection\Method`)
that start with `"get"` or one of the given prefixes if you passed a list
or prefixes in the constructor.

```php
class User
{
    public function getName(): string { return 'Bob'; }
    public function isAdmin(): bool { return false; }
    public function update(): void {}
}

$getGetters = new \Morph\Getters(['get', 'is']);
$getGetters(User::class);
```
```php
[
    'Name' => new \Morph\Reflection\Method(new \ReflectionMethod(
        User::class, 'getName',
    )),
    'Admin' => new \Morph\Reflection\Method(new \ReflectionMethod(
        User::class, 'isAdmin',
    )),
]
```

Note that `Getters` does not call the methods, it just return
the definitions of those methods.

See more in the [Reflection chapter](#Reflection).

### GettersToArray

Return values for each public method of an object
that start with `"get"` or one of the given prefixes if you passed a list
or prefixes in the constructor.

```php
class User
{
    public string $id = 'abc';
    public function getName(): string { return 'Bob'; }
    public function isAdmin(): bool { return false; }
    public function update(): void {}
}

$bob = new User();

$getGetters = new \Morph\GettersToArray(['get', 'is']);
$getGetters($bob);
```
```php
[
    'Name' => 'Bob',
    'Admin' => false,
]
```

### LowerFirstLetter

Lowercase the first letter of the input if it's a string.
If a mapping array is given in the constructor, this will
be used prior to the lowercase action.

```php
$lowerFirstLetter = new \Morph\LowerFirstLetter([
    'Special' => '***special***',
]);

$lowerFirstLetter(5); // 5, non-string input are returned as is
$lowerFirstLetter('FooBar'); // "fooBar"
$lowerFirstLetter('Special'); // "***special***"
```

### UpperFirstLetter

Uppercase the first letter of the input if it's a string.
If a mapping array is given in the constructor, this will
be used prior to the uppercase action.

```php
$upperFirstLetter = new \Morph\UpperFirstLetter([
    '***special***' => 'Special',
]);

$upperFirstLetter(5); // 5, non-string input are returned as is
$upperFirstLetter('fooBar'); // "FooBar"
$upperFirstLetter('***special***'); // "Special"
```

### LowerKeysFirstLetter

Lowercase the first letter of each key of a given array.
If a mapping array is given in the constructor, this will
be used prior to the lowercase action.

```php
$lowerFirstLetter = new \Morph\LowerKeysFirstLetter([
    'Special' => '***special***',
]);

$lowerFirstLetter([
    5 => 'abc',
    'FooBar' => 'def',
    'Special' => 'ghi',
]);
```
```php
[
    5 => 'abc',
    'fooBar' => 'def',
    '***special***' => 'ghi',
]
```

### UpperKeysFirstLetter

Uppercase the first letter of each key of a given array.
If a mapping array is given in the constructor, this will
be used prior to the uppercase action.

```php
$upperFirstLetter = new \Morph\UpperKeysFirstLetter([
    '***special***' => 'Special',
]);

$upperFirstLetter([
    5 => 'abc',
    'fooBar' => 'def',
    '***special***' => 'ghi',
]);
```
```php
[
    5 => 'abc',
    'FooBar' => 'def',
    'Special' => 'ghi',
]
```

### Merge

Merge (using `array_merge`) the results of a list of transformers.

```php
$itemsWithTotal = new \Morph\Merge([
    static fn ($value) => ['items' => $value],
    static fn ($value) => ['total' => count($value)],
]);

$itemsWithTotal(['A', 'B']);
```
```php
[
    'items' => ['A', 'B'],
    'total' => 2,
]
```

It will be mostly useful to combine other `Morph` classes:
```php
class User
{
    public string $id = 'abc';
    public function getName(): string { return 'Bob'; }
    public function isAdmin(): bool { return false; }
    public function update(): void {}
}

$bob = new User();

$itemsWithTotal = new \Morph\Merge([
    new \Morph\PublicPropertiesToArray(),
    new \Morph\GettersToArray(['get', 'is']),
]);

$itemsWithTotal(['A', 'B']);
```
```php
[
    'id' => 'abc',
    'Name' => 'Bob',
    'Admin' => false,
]
```

### Only

Keep from an array only the given keys.
```php
$info = [
    'firstName' => 'Georgia',
    'lastName' => 'Dobbins',
    'group' => 'The Marvelettes',
];

$select = new \Morph\Only(['firstName', 'lastName']);

$select($info);
```
```php
[
    'firstName' => 'Georgia',
    'lastName' => 'Dobbins',
]
```

It can be an array or a single key:
```php
$info = [
    'firstName' => 'Georgia',
    'lastName' => 'Dobbins',
    'group' => 'The Marvelettes',
];

$select = new \Morph\Only('firstName');

$select($info);
```
```php
[
    'firstName' => 'Georgia',
]
```

### Pick

Return the value at a given key or null if the key does not exist.
```php
$info = [
    'firstName' => 'Georgia',
    'lastName' => 'Dobbins',
    'group' => 'The Marvelettes',
];

$select = new \Morph\Pick('firstName');

$select($info);
```
```php
'Georgia'
```

### Properties

Return the list of the properties defined in a class
(as an array of `\Morph\Reflection\Property`)

```php
class User
{
    public string $name;
    protected int $id;
    private array $cache;
}

$getProperties = new \Morph\Properties();

$getProperties(User::class);
```
```php
[
    'name' => new \Morph\Reflection\Property(new \ReflectionProperty(
        User::class, 'name',
    )),
    'id' => new \Morph\Reflection\Property(new \ReflectionProperty(
        User::class, 'id',
    )),
    'cache' => new \Morph\Reflection\Property(new \ReflectionProperty(
        User::class, 'cache',
    )),
]
```

See more in the [Reflection chapter](#Reflection).

### PublicProperties

Return the list of the public properties defined in a class
(as an array of `\Morph\Reflection\Property`)

```php
class User
{
    public string $name;
    protected int $id;
    private array $cache;
}

$getPublicProperties = new \Morph\PublicProperties();

$getPublicProperties(User::class);
```
```php
[
    'name' => new \Morph\Reflection\Property(new \ReflectionProperty(
        User::class, 'name',
    )),
]
```

See more in the [Reflection chapter](#Reflection).

### PublicPropertiesToArray

Return the list of the public properties defined in a class
(as an array of `\Morph\Reflection\Property`)

```php
class User
{
    public string $name;
    protected int $id;
    private array $cache;

    public function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
        $this->cache = ['foo' => 'bar'];
    }
}

$getPublicValues = new \Morph\PublicPropertiesToArray();

$getPublicValues(new User('Juanita Cowart'));
```
```php
[
    'name' => 'Juanita Cowart',
]
```

### Sequence

Group transformation and execute them in the given order.
Each transformation receive as input the result of the previous
transformation.

```php
$data = [
    'singer' => [
        'firstName' => 'Ann',
        'lastName' => 'Bogan',
    ],
    'label' => [
        'name' => 'Motown',
    ],
];

$getSingerLastName = new \Morph\Sequence([
    new \Morph\Pick('singer'),
    new \Morph\Pick('lastName'),
]);

$getSingerLastName($data);
```
```php
'Bogan'
```

### TransformKeys

Transform each key of an array using the given transformation.

```php
$data = [
    'first_name' => 'Ann',
    'last_name' => 'Bogan',
];

$upperKeys = new \Morph\TransformKeys('mb_strtoupper');
```
```php
[
    'FIRST_NAME' => 'Ann',
    'LAST_NAME' => 'Bogan',
]
```

### TransformValues

Transform each value of an array using the given transformation.

```php
$data = [
    'first_name' => 'Ann',
    'last_name' => 'Bogan',
];

$upperKeys = new \Morph\TransformValues('mb_strtoupper');
```
```php
[
    'first_name' => 'ANN',
    'last_name' => 'BOGAN',
]
```

Transform each value of an array using the given transformation.

### MorphBase

Abstract `MorphBase` that can be extended to craft new transformations
and inherit from handy methods:

```php
class UserTransformer extends \Morph\MorphBase
{
    private $nameTransformer;
    private $defaultTransformer;

    public function __construct($nameTransformer, $defaultTransformer)
    {
        $this->nameTransformer = $nameTransformer;
        $this->defaultTransformer = $defaultTransformer;
    }

    public function __invoke(User $user): array
    {
        $data = $this->mapWithTransformer($this->defaultTransformer, [
            'group' => $user->getGroup(),
            'label' => $user->getLabel(),
        ];

        return [
            'name' => $this->useTransformerWith($this->nameTransformer, $user->getName()),
        ];
    }
}
```
`useTransformerWith()` can use as a transformer either any callable or
a class instance that have a `transform()` method.

`mapWithTransformer()` is the same but take an array and apply the
transformer to each value of this array.

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

### Iteration

When a transformation is iterable (`Morph\Iteration\*Iterable` classes),
or a Sequence of iterable transformations and get passed an iterable value
(`Traversable`, `Generator`, etc.), it will proceed lazily, so it won't
start the iteration but return a new iterable and transformation will be
proceeded as you iterate on the returned iterable value.

Every iterable can also take array values and then use `array_*` functions
for better performance.

## Transformation

`Morph\Transformation` is a build object, it allows to prepare a transformation
with multiple steps using chaining. It's handy when wanting to optimize
memory consumption long iteration (reading line by line a big log file for
instance).

As it's a lazy builder, it won't start any actual transformation until you
call `->get()` on it.

```php
function gen() {
    yield 1;
    yield 2;
    yield 3;
    yield 4;
    yield 5;
    yield 6;
}

var_dump(
    \Morph\Transformation::take(gen())
        ->filter(static fn (int $number) => $number !== 4)
        ->map(static fn (int $number) => [
            'number' => $number,
            'odd' => $number % 2 === 0,
        ])
        ->filter(key: 'odd')
        ->values()
        ->array()
        ->get()
);
```

Output:
```
array(2) {
  [0] =>
  array(2) {
    'number' =>
    int(2)
    'odd' =>
    bool(true)
  }
  [1] =>
  array(2) {
    'number' =>
    int(6)
    'odd' =>
    bool(true)
  }
}
```

## CountIterable

Count iterations (use `count` on `Countable` values) otherwise iterate.

```php
function gen() {
    yield 1;
    yield 2;
}

echo (new \Morph\Iteration\CountIterable())(gen()); // 2
```

Use `->count()` on `Transformation` builder object to add it as a step.

## SumIterable

Count iterations (use `array_sum` on `array` value) otherwise iterate.

```php
function gen() {
    yield 3;
    yield 2;
}

echo (new \Morph\Iteration\SumIterable())(gen()); // 5
```

Use `->sum()` on `Transformation` builder object to add it as a step.

## ValuesIterable

Get values (use `array_values` on `array` value) otherwise iterate
dropping indexes.

```php
function gen() {
    yield 'A' => 3;
    yield 'B' => 2;
}

foreach ((new \Morph\Iteration\ValuesIterable())(gen()) as $key => $value) {
    echo "$key: $value\n";
}
```

As keys are dropped, you get the output:
```
0: 3
1: 2
```

Use `->values()` on `Transformation` builder object to add it as a step.

## KeysIterable

Get values (use `array_keys` on `array` value) otherwise iterate
dropping input values and yielding indexes as output values.

```php
function gen() {
    yield 'A' => 3;
    yield 'B' => 2;
}

foreach ((new \Morph\Iteration\ValuesIterable())(gen()) as $key => $value) {
    echo "$key: $value\n";
}
```

Output:
```
0: A
1: B
```

Use `->keys()` on `Transformation` builder object to add it as a step.

## FilterIterable

Filter iterable value keeping only items matching a given filter.

```php
function gen() {
    yield 'A' => 3;
    yield 'B' => 2;
}

foreach ((new \Morph\Iteration\FilterIterable(
    static fn (int $number) => $number % 2 === 0,
))(gen()) as $key => $value) {
    echo "$key: $value\n";
}
```

Only values matching the callback remain:
```
B: 2
```

Alternatively, `FilterIterable` can also take a `property` or
`key` named argument:

`FilterIterable(property: 'active')` is equivalent to:
`FilterIterable(static fn ($item) => $item->active ?? false)`

`FilterIterable(key: 'active')` is equivalent to:
`FilterIterable(static fn ($item) => $item['active'] ?? false)`

Additionally, you can drop indexes when filtering (in the same
loop) by setting `dropIndex: true` argument.

`FilterIterable()` (with no callback, property nor key) will
keep truthy elements.

Use `->filter(...)` on `Transformation` builder object to add it as a step.

## FlipIterable

Flip keys and values (use `array_flip` on `array` value) otherwise iterate.

```php
function gen() {
    yield 'A' => 3;
    yield 'B' => 2;
    yield 'A' => 5;
    yield 'B' => 3;
}

foreach ((new \Morph\Iteration\FlipIterable())(gen()) as $key => $value) {
    echo "$key: $value\n";
}
```

Output:
```
3: A
2: B
5: A
3: B
```

Use `->flip()` on `Transformation` builder object to add it as a step.

## MapIterable

Transform each value of an iterable with a transformation callback.

The callback receive first the value, second the index, then extra
arguments as passed when invoking the transformer.

```php
function gen() {
    yield 'A' => 3;
    yield 'B' => 2;
}

foreach ((new \Morph\Iteration\MapIterable(
    static fn (int $number) => $number * 2,
))(gen()) as $key => $value) {
    echo "$key: $value\n";
}

foreach ((new \Morph\Iteration\MapIterable(
    static fn (int $number, string $letter, string $x) => "$letter/$number/$x",
))(gen(), 'x') as $key => $value) {
    echo "$value\n";
}
```

Output:
```
A: 6
B: 4
A/6/x
B/4/x
```

Alternatively, `MapIterable` can also take a `property` or
`key` named argument:

`MapIterable(property: 'active')` is equivalent to:
`MapIterable(static fn ($item) => $item->active ?? false)`

`MapIterable(key: 'active')` is equivalent to:
`MapIterable(static fn ($item) => $item['active'] ?? false)`

Use `->map(...)` on `Transformation` builder object to add it as
a step.

## ReduceIterable

Applies iteratively the callback function to the elements of the
array/iterable, to reduce it to a single value
(use `array_reduce` on `array` value) otherwise iterate.

```php
function gen() {
    yield 3;
    yield 2;
    yield 6;
}

echo (new \Morph\Iteration\ReduceIterable(
    static fn ($carry, $item) => $carry * $item,
))(gen(), 1); // 36
```

Initial value might be passed either on construct or on invocation.

Use `->reduce()` on `Transformation` builder object to add it as a step.
