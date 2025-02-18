<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Sets\DoctrineSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineSet::class)]
final class DoctrineSetTest extends TestCase
{
    public function testWithSets(): void
    {
        $config = new Config();
        $set = new DoctrineSet();

        $set($config);

        self::assertCount(2, $config->getFilters());
        self::assertEmpty($config->getIgnoredClasses());
        self::assertSame(
            ['Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener'],
            $config->getIgnoredAttributes()
        );
    }
}
