<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Filter\Symfony\AutoconfigureTagAttributeFilter;
use DBublik\UnusedClassHunter\PreFilter\Symfony\ConstraintPreFilter;
use DBublik\UnusedClassHunter\Sets\SymfonySet;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SymfonySet::class)]
final class SymfonySetTest extends AbstractSetTestCase
{
    public function testWithSets(): void
    {
        $set = new SymfonySet();

        self::assertSet(
            $set,
            preFilters: [new ConstraintPreFilter()],
            filters: [new AutoconfigureTagAttributeFilter()],
            ignoredClasses: [
                'Symfony\Component\EventDispatcher\EventSubscriberInterface',
                'Symfony\Component\Form\FormTypeExtensionInterface',
                'Symfony\Component\Validator\ConstraintValidatorInterface',
            ],
            ignoredAttributes: [
                'Symfony\Component\Console\Attribute\AsCommand',
                'Symfony\Component\Routing\Attribute\Route',
            ],
        );
    }
}
