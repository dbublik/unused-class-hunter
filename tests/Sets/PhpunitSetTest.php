<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Sets\PhpunitSet;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpunitSet::class)]
final class PhpunitSetTest extends AbstractSetTestCase
{
    public function testWithSets(): void
    {
        $set = new PhpunitSet();

        self::assertSet(
            $set,
            ignoredClasses: ['PHPUnit\Framework\TestCase'],
        );
    }
}
