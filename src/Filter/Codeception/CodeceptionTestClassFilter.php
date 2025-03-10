<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter\Codeception;

use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

final readonly class CodeceptionTestClassFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        if (!str_ends_with($class->getName(), 'Cest')) {
            return false;
        }

        $extends = $class->getExtends();

        if (0 === $count = \count($extends)) {
            return true;
        }

        return 1 === $count && str_ends_with($extends[0], 'Cest');
    }
}
