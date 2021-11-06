<?php

declare(strict_types=1);

namespace Morph\Reflection;

use Reflector;

interface Documented
{
    /**
     * Returns the native PHP Reflector (ReflectionMethod or ReflectionProperty).
     */
    public function getReflection(): Reflector;

    /**
     * Returns the name of the type if specified (property type or method return type).
     */
    public function getTypeName(): ?string;

    /**
     * Returns the PHPDoc cleaned from slashes, asterisk, trailing whitespace and annotations.
     */
    public function getDescription(): string;

    /**
     * Returns the PHPDoc cleaned from slashes, asterisk and trailing whitespace.
     */
    public function getDescriptionWithAnnotations(): string;

    /**
     * Return true if the PHPDoc contains a given "@" annotation, false else.
     */
    public function isAnnotatedWith(string $annotation, bool $caseInsensitive): bool;

    /**
     * Return true if property/method is targeted by a given attribute (PHP >= 8 needed), false else.
     */
    public function hasAttribute(string $attribute): bool;

    /**
     * Return true if property/method is targeted by a given attribute (PHP >= 8 needed)
     * or if the PHPDoc contains a given "@" annotation, false else.
     */
    public function hasAttributeOrIsAnnotatedWith(string $attribute): bool;
}
