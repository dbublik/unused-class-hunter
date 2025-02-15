<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

final readonly class AttributeFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        if (
            ([] === $attributes = $class->getAttributes())
            || ([] === $ignoredAttributes = $reader->getConfig()->getIgnoredAttributes())
        ) {
            return false;
        }

        foreach ($ignoredAttributes as $ignoredAttribute) {
            foreach ($attributes as $attribute) {
                if (is_a($attribute, $ignoredAttribute, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
