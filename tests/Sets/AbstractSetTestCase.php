<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\PreFilter\PreFilterInterface;
use DBublik\UnusedClassHunter\Sets\SetInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractSetTestCase extends TestCase
{
    /**
     * @param list<PreFilterInterface> $preFilters
     * @param list<FilterInterface> $filters
     * @param list<string> $ignoredClasses
     * @param list<string> $ignoredAttributes
     */
    final public static function assertSet(
        SetInterface $set,
        array $preFilters = [],
        array $filters = [],
        array $ignoredClasses = [],
        array $ignoredAttributes = [],
    ): void {
        $config = new Config();

        $set($config);

        self::assertSame(
            array_map(
                static fn (PreFilterInterface $preFilter): string => $preFilter::class,
                $preFilters
            ),
            array_map(
                static fn (PreFilterInterface $preFilter): string => $preFilter::class,
                $config->getPreFilters()
            )
        );
        self::assertSame(
            array_map(
                static fn (FilterInterface $preFilter): string => $preFilter::class,
                [new ClassFilter(), new AttributeFilter(), ...$filters]
            ),
            array_map(
                static fn (FilterInterface $preFilter): string => $preFilter::class,
                $config->getFilters()
            )
        );
        self::assertSame($ignoredClasses, $config->getIgnoredClasses());
        self::assertSame($ignoredAttributes, $config->getIgnoredAttributes());
    }
}
