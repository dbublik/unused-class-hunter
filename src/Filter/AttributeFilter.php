<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Filter;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;

final readonly class AttributeFilter implements FilterInterface
{
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
