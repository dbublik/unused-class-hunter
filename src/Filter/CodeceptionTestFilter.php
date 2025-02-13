<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use DBublik\UnusedClassHunter\ValueObject\ParseInformation;

final readonly class CodeceptionTestFilter implements FilterInterface
{
    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
    {
        if (!str_ends_with((string) $class->getClassName(), 'Cest')) {
            return false;
        }

        $extends = $class->getExtends();
        $count = \count($extends);

        return (0 === $count)
            || (1 === $count && str_ends_with($extends[0], 'Cest'));
    }
}
