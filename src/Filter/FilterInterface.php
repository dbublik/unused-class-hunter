<?php

namespace DBublik\UnusedClass\Filter;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;

interface FilterInterface
{
    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool;
}
