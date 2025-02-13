<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AutoconfigureTagAttributeFilter;

final readonly class SymfonySet implements SetInterface
{
    #[\Override]
    public function __invoke(Config $config): void
    {
        $config->withFilters(new AutoconfigureTagAttributeFilter());
        $config->withIgnoredClasses(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\Form\FormTypeExtensionInterface',
            'Symfony\Component\Validator\ConstraintValidatorInterface',
        );
        $config->withIgnoredAttributes(
            'Symfony\Component\Console\Attribute\AsCommand',
            'Symfony\Component\Routing\Attribute\Route',
        );
    }
}
