<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

/**
 * @api
 */
final readonly class AsAliasAttributeFilter implements FilterInterface
{
    private const AS_ALIAS_CLASS = 'Symfony\Component\DependencyInjection\Attribute\AsAlias';

    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        if (!\in_array(self::AS_ALIAS_CLASS, $class->getAttributes(), true)) {
            return false;
        }

        $classes = array_merge($class->getExtends(), $class->getImplements());

        if (1 !== \count($classes)) {
            return false;
        }

        $baseClass = $classes[0];

        $usedFiles = $reader->getUsedFilesByName($baseClass);
        $usedFile = array_shift($usedFiles);

        if (null === $usedFile) {
            return false;
        }

        if (
            [] !== $usedFiles
            || !$usedFile instanceof ClassNode
        ) {
            return true;
        }

        return $usedFile->getName() !== $class->getName();
    }
}
