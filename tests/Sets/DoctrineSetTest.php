<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Sets;

use DBublik\UnusedClassHunter\Sets\DoctrineSet;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DoctrineSet::class)]
final class DoctrineSetTest extends AbstractSetTestCase
{
    public function testWithSets(): void
    {
        $set = new DoctrineSet();

        self::assertSet(
            $set,
            ignoredAttributes: ['Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener'],
        );
    }
}
