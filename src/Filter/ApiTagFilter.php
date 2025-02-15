<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

/**
 * @api
 */
final readonly class ApiTagFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        return $class->hasApiTag();
    }
}
