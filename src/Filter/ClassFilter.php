<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Filter;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;

final class ClassFilter implements FilterInterface
{
    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
    {
        foreach ($config->getIgnoredClasses() as $ignoredClass) {
            if ($ignoredClass === $class->getClassName() || is_a($class->getClassName(), $ignoredClass, true)) {
                return true;
            }
        }

        return false;
    }
}
