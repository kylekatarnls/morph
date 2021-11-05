<?php

declare(strict_types=1);

namespace Morph;

use Morph\Reflection\Property;
use ReflectionClass;
use ReflectionException;

class PublicProperties extends MorphBase
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
            if ($property->isPublic()) {
                $result[$property->getName()] = new Property($property);
            }
        }

        return $result;
    }
}
