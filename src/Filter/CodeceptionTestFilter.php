<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Filter;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;

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
