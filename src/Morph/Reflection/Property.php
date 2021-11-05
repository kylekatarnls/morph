<?php

declare(strict_types=1);

namespace Morph\Reflection;

use ReflectionProperty;

class Property implements Documented
{
    use DocumentationTrait;

    private ReflectionProperty $reflectionProperty;

    public function __construct(ReflectionProperty $reflectionProperty)
    {
        $this->reflectionProperty = $reflectionProperty;
    }

    public function getReflection(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getTypeName(): ?string
    {
        $type = $this->reflectionProperty->getType();

        return $type ? $type->getName() : null;
    }
}
