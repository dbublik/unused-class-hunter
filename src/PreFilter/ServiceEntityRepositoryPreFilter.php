<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\PreFilter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

/**
 * @api
 */
final readonly class ServiceEntityRepositoryPreFilter implements PreFilterInterface
{
    private const REPOSITORY_CLASS = 'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository';
    private const TABLE_ATTRIBUTE = 'Doctrine\ORM\Mapping\Table';

    public function isUnused(ClassNode $class, ReaderResult $reader): bool
    {
        if (!is_a($class->getName(), self::REPOSITORY_CLASS, true)) {
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

        return \in_array(self::TABLE_ATTRIBUTE, $fileNode->getAttributes(), true);
    }
}
