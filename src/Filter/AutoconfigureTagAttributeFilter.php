<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Filter;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;

final readonly class AutoconfigureTagAttributeFilter implements FilterInterface
{
    private const string AUTOCONFIGURE_TAG_CLASS = '\Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag';

    public function __construct(
        private int $maxDeep = 2,
    ) {}

    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
    {
        if ($this->hasAttribute($class)) {
            return true;
        }

        return $this->hasParentAttribute($class, $information);
    }

    private function hasAttribute(FileInformation $class): bool
    {
        foreach ($class->getAttributes() as $attribute) {
            if (is_a($attribute, self::AUTOCONFIGURE_TAG_CLASS, true)) {
                return true;
            }
        }

        return false;
    }

    private function hasParentAttribute(FileInformation $class, ParseInformation $information, int $deep = 0): bool
    {
        ++$deep;

        foreach (array_merge($class->getExtends(), $class->getImplements()) as $className) {
            if (null === $parent = $information->getClassByClassName($className)) {
                continue;
            }

            if ($this->hasAttribute($parent)) {
                return true;
            }

            if (
                ($this->maxDeep > $deep)
                && $this->hasParentAttribute($parent, $information, $deep)
            ) {
                return true;
            }
        }

        return false;
    }
}
