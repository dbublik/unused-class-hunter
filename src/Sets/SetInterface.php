<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Config;

interface SetInterface
{
    public function __invoke(Config $config): void;
}
