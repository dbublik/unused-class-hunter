<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use DBublik\UnusedClassHunter\ValueObject\ParseInformation;

final readonly class AttributeFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
    {
        foreach ($class->getAttributes() as $attribute) {
            foreach ($config->getIgnoredAttributes() as $ignoredAttribute) {
                if (is_a($attribute, $ignoredAttribute, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
