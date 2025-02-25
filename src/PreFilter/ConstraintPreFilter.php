<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\PreFilter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

final readonly class ConstraintPreFilter implements PreFilterInterface
{
    private const CONSTRAINT_CLASS = 'Symfony\Component\Validator\Constraint';

    public function isUnused(ClassNode $class, ReaderResult $reader): bool
    {
        if (!is_a($class->getName(), self::CONSTRAINT_CLASS, true)) {
            return false;
        }

        $usedByFileNodes = $reader->getUsedFilesByName($class->getName());
        $fileNode = array_shift($usedByFileNodes);

        if (
            [] !== $usedByFileNodes
            || !$fileNode instanceof ClassNode
        ) {
            return false;
        }

        return $fileNode->getName() === $class->getName() . 'Validator';
    }
}
