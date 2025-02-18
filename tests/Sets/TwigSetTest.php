<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Sets\TwigSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TwigSet::class)]
final class TwigSetTest extends TestCase
{
    public function testWithSets(): void
    {
        $config = new Config();
        $set = new TwigSet();

        $set($config);

        self::assertCount(2, $config->getFilters());
        self::assertSame(['Twig\Extension\ExtensionInterface'], $config->getIgnoredClasses());
        self::assertEmpty($config->getIgnoredAttributes());
    }
}
