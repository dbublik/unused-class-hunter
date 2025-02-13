<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Config;

final readonly class DoctrineSet implements SetInterface
{
    #[\Override]
    public function __invoke(Config $config): void
    {
        $config->withIgnoredAttributes('Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener');
    }
}
