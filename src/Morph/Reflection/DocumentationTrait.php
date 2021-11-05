<?php

declare(strict_types=1);

namespace Morph\Reflection;

trait DocumentationTrait
{
    private string $descriptionWithAnnotations;
    private string $description;

    public function getDescription(): string
    {
        return $this->description ??= trim(preg_replace(
            '/^@(\w+)(.*)(\n([ ]{4,}|\t+)\S.*)*$/m',
            '',
            $this->getDescriptionWithAnnotations(),
        ));
    }

    public function getDescriptionWithAnnotations(): string
    {
        return $this->descriptionWithAnnotations ??= preg_replace('/^\s*\*(?: |$)/m', '', trim(preg_replace(
            '/^\/\*+([\s\S]*)\*\/$/',
            '$1',
            str_replace("\r", '', $this->getReflection()->getDocComment() ?: ''),
        )));
    }

    public function isAnnotatedWith(string $annotation, bool $caseInsensitive): bool
    {
        return (bool) preg_match(
            '/^@' . $annotation . '/m' . ($caseInsensitive ? 'i' : ''),
            $this->getDescriptionWithAnnotations(),
        );
    }

    public function hasAttribute(string $attribute): bool
    {
        $reflection = $this->getReflection();

        return method_exists($reflection, 'getAttributes') && count($reflection->getAttributes($attribute));
    }

    public function hasAttributeOrIsAnnotatedWith(string $attribute): bool
    {
        return $this->hasAttribute($attribute)
            || $this->isAnnotatedWith(preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', $attribute), true);
    }
}
