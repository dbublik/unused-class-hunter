<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

use DBublik\UnusedClassHunter\Console\Reporter\ReportSummary;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportSummary::class)]
final class ReportSummaryTest extends TestCase
{
    public function testConstructorEmpty(): void
    {
        $summary = new ReportSummary(
            unusedClasses: [],
        );

        self::assertEmpty($summary->unusedClasses);
        self::assertSame(0.0, $summary->duration);
        self::assertSame(0, $summary->memory);
        self::assertFalse($summary->isDeletable);
        self::assertTrue($summary->isDecoratedOutput);
    }

    public function testConstructor(): void
    {
        $summary = new ReportSummary(
            unusedClasses: [
                $classNode = new ClassNode('file.php', [], self::class, 1),
            ],
            duration: 100.25,
            memory: 12_345_678,
            isDeletable: true,
            isDecoratedOutput: false,
        );

        self::assertCount(1, $summary->unusedClasses);
        self::assertSame($classNode, $summary->unusedClasses[0]);
        self::assertSame(100.25, $summary->duration);
        self::assertSame(12_345_678, $summary->memory);
        self::assertTrue($summary->isDeletable);
        self::assertFalse($summary->isDecoratedOutput);
    }
}
