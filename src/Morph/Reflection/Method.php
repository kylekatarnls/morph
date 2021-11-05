<?php

declare(strict_types=1);

namespace Morph\Reflection;

use ReflectionMethod;

class Method implements Documented
{
    use DocumentationTrait;

    private ReflectionMethod $reflectionMethod;

    public function __construct(ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
    }

    public function getReflection(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    public function getReturnTypeName(): ?string
    {
        $type = $this->reflectionMethod->getReturnType();

        return $type ? $type->getName() : null;
    }

    public function getTypeName(): ?string
    {
        return $this->getReturnTypeName();
    }
}
