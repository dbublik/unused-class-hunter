<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Filter\CodeceptionTestClassFilter;
use DBublik\UnusedClassHunter\Sets\CodeceptionSet;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CodeceptionSet::class)]
final class CodeceptionSetTest extends AbstractSetTestCase
{
    public function testWithSets(): void
    {
        $set = new CodeceptionSet();

        self::assertSet(
            $set,
            filters: [new CodeceptionTestClassFilter()],
            ignoredClasses: ['Codeception\Module'],
        );
    }
}
