<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

final class ClassFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        foreach ($reader->getConfig()->getIgnoredClasses() as $ignoredClass) {
            if (
                $ignoredClass === $class->getName()
                || is_a($class->getName(), $ignoredClass, true)
            ) {
                return true;
            }
        }

        return false;
    }
}
