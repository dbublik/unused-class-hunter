<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

use DBublik\UnusedClassHunter\ValueObject\FileInformation;

interface ReporterInterface
{
    public function getFormat(): string;

    /**
     * @param list<FileInformation> $unusedClasses
     */
    public function generate(array $unusedClasses): string;
}
