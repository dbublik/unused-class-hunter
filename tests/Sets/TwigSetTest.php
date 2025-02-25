<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Sets\TwigSet;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TwigSet::class)]
final class TwigSetTest extends AbstractSetTestCase
{
    public function testWithSets(): void
    {
        $set = new TwigSet();

        self::assertSet(
            $set,
            ignoredClasses: ['Twig\Extension\ExtensionInterface'],
        );
    }
}
