<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Tests\Console\ConfigurationResolverTest;

return (new Config())
    ->withIgnoredClasses(ConfigurationResolverTest::class);

