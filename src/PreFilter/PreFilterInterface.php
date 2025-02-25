<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\PreFilter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

interface PreFilterInterface
{
    public function isUnused(ClassNode $class, ReaderResult $reader): bool;
}
