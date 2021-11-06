<?php

declare(strict_types=1);

namespace Morph\Tests\Fixtures;

class ModelTransformer extends \Morph\Sequence
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
            new \Morph\PublicPropertiesToArray(),
            'array_filter',
            new \Morph\UpperKeysFirstLetter(['id' => 'ID']),
        ];
    }
}
