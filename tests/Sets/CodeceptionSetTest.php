<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\CodeceptionTestClassFilter;
use DBublik\UnusedClassHunter\Sets\CodeceptionSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CodeceptionSet::class)]
final class CodeceptionSetTest extends TestCase
{
    public function testWithSets(): void
    {
        $config = new Config();
        $set = new CodeceptionSet();

        $set($config);

        self::assertCount(3, $filters = $config->getFilters());
        self::assertInstanceOf(CodeceptionTestClassFilter::class, $filters[2]);
        self::assertSame(['Codeception\Module'], $config->getIgnoredClasses());
        self::assertEmpty($config->getIgnoredAttributes());
    }
}
