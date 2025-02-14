<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use DBublik\UnusedClassHunter\ValueObject\ParseInformation;

/**
 * @api
 */
final readonly class ApiTagFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
    {
        return $class->hasClassApiTag();
    }
}
