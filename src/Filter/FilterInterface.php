<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

interface FilterInterface
{
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool;
}
