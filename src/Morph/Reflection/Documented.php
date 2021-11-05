<?php

declare(strict_types=1);

namespace Morph\Reflection;

use Reflector;

interface Documented
{
    public function getReflection(): Reflector;

    public function getTypeName(): ?string;

    public function getDescription(): string;

    public function getDescriptionWithAnnotations(): string;

    public function isAnnotatedWith(string $annotation, bool $caseInsensitive): bool;

    public function hasAttribute(string $attribute): bool;

    public function hasAttributeOrIsAnnotatedWith(string $attribute): bool;
}
