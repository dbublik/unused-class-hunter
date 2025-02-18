<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Sets\PhpunitSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PhpunitSet::class)]
final class PhpunitSetTest extends TestCase
{
    public function testWithSets(): void
    {
        $config = new Config();
        $set = new PhpunitSet();

        $set($config);

        self::assertCount(2, $config->getFilters());
        self::assertSame(['PHPUnit\Framework\TestCase'], $config->getIgnoredClasses());
        self::assertEmpty($config->getIgnoredAttributes());
    }
}
