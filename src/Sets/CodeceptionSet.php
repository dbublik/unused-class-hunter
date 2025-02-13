<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\CodeceptionTestFilter;

final readonly class CodeceptionSet implements SetInterface
{
    #[\Override]
    public function __invoke(Config $config): void
    {
        $config->withFilters(new CodeceptionTestFilter());
        $config->withIgnoredClasses('Codeception\Module');
    }
}
