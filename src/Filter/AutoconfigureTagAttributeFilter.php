<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

final readonly class AutoconfigureTagAttributeFilter implements FilterInterface
{
    private const string AUTOCONFIGURE_TAG_CLASS = '\Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag';
    private const int MAX_DEEP_DEFAULT = 3;

    public function __construct(
        private int $maxDeep = self::MAX_DEEP_DEFAULT,
    ) {}

    #[\Override]
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        if ($this->hasAttribute($class)) {
            return true;
        }

        return $this->hasParentAttribute($class, $reader);
    }

    private function hasAttribute(ClassNode $class): bool
    {
        foreach ($class->getAttributes() as $attribute) {
            if (
                self::AUTOCONFIGURE_TAG_CLASS === $attribute
                || is_a($attribute, self::AUTOCONFIGURE_TAG_CLASS, true)
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasParentAttribute(ClassNode $class, ReaderResult $reader, int $deep = 0): bool
    {
        ++$deep;

        foreach (array_merge($class->getExtends(), $class->getImplements()) as $name) {
            if (null === $parent = $reader->getClassByName($name)) {
                continue;
            }

            if ($this->hasAttribute($parent)) {
                return true;
            }

            if (
                ($this->maxDeep > $deep)
                && $this->hasParentAttribute($parent, $reader, $deep)
            ) {
                return true;
            }
        }

        return false;
    }
}
