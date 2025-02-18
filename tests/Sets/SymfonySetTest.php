<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AutoconfigureTagAttributeFilter;
use DBublik\UnusedClassHunter\Sets\SymfonySet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SymfonySet::class)]
final class SymfonySetTest extends TestCase
{
    public function testWithSets(): void
    {
        $config = new Config();
        $set = new SymfonySet();

        $set($config);

        self::assertCount(3, $filters = $config->getFilters());
        self::assertInstanceOf(AutoconfigureTagAttributeFilter::class, $filters[2]);
        self::assertSame(
            [
                'Symfony\Component\EventDispatcher\EventSubscriberInterface',
                'Symfony\Component\Form\FormTypeExtensionInterface',
                'Symfony\Component\Validator\ConstraintValidatorInterface',
            ],
            $config->getIgnoredClasses()
        );
        self::assertSame(
            [
                'Symfony\Component\Console\Attribute\AsCommand',
                'Symfony\Component\Routing\Attribute\Route',
            ],
            $config->getIgnoredAttributes()
        );
    }
}
