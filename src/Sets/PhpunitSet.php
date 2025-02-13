<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Config;

final readonly class PhpunitSet implements SetInterface
{
    #[\Override]
    public function __invoke(Config $config): void
    {
        $config->withIgnoredClasses('PHPUnit\Framework\TestCase');
    }
}
