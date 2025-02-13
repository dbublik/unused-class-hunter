<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Sets;

use DBublik\UnusedClassHunter\Filter\AutoconfigureTagAttributeFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;

final readonly class SymfonySet extends AbstractSet
{
    /**
     * @return iterable<class-string, FilterInterface>
     */
    public function getFilters(): iterable
    {
        return [
            new AutoconfigureTagAttributeFilter(),
        ];
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredClasses(): iterable
    {
        return [
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\Form\FormTypeExtensionInterface',
            'Symfony\Component\Validator\ConstraintValidatorInterface',
        ];
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredAttributes(): iterable
    {
        return [
            'Symfony\Component\Console\Attribute\AsCommand',
            'Symfony\Component\Routing\Attribute\Route',
        ];
    }
}
