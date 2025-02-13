<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Console\Reporter;

use DBublik\UnusedClass\ValueObject\FileInformation;

interface ReporterInterface
{
    public function getFormat(): string;

    /**
     * @param list<FileInformation> $unusedClasses
     */
    public function generate(array $unusedClasses): string;
}
