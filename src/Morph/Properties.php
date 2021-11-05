<?php

declare(strict_types=1);

namespace Morph;

use Morph\Reflection\Property;
use ReflectionClass;
use ReflectionException;

class Properties extends MorphBase
{
    /**
     * @param object|string $value
     * @throws ReflectionException
     */
    public function __invoke($value, ...$args): array
    {
        $result = [];
        $classReflection = new ReflectionClass($value);

        foreach ($classReflection->getProperties() as $property) {
            $result[$property->getName()] = new Property($property);
        }

        return $result;
    }
}
