<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Config;

final readonly class TwigSet implements SetInterface
{
    #[\Override]
    public function __invoke(Config $config): void
    {
        $config->withIgnoredClasses('Twig\Extension\ExtensionInterface');
    }
}
