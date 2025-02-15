<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;

interface ReporterInterface
{
    public function getFormat(): string;

    /**
     * @param list<ClassNode> $unusedClasses
     *
     * @return non-empty-string
     */
    public function generate(array $unusedClasses): string;
}
