<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

use DBublik\UnusedClassHunter\Console\Reporter\ReportSummary;
use DBublik\UnusedClassHunter\Console\Reporter\TextReporter;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TextReporter::class)]
final class TextReporterTest extends TestCase
{
    public function testGetFormat(): void
    {
        $reporter = new TextReporter();

        self::assertSame('text', $reporter->getFormat());
    }

    #[DataProvider('provideGenerate')]
    public function testGenerate(ReportSummary $reportSummary, string $expectedReport): void
    {
        $reporter = new TextReporter();

        $report = $reporter->generate($reportSummary);

        self::assertSame($expectedReport, $report);
    }

    /**
     * @return iterable<array{0: ReportSummary, 1: string}>
     */
    public static function provideGenerate(): iterable
    {
        yield [
            new ReportSummary([]),
            '<info>Success! The hunt is over — no unused classes found.</info>

',
        ];

        yield [
            new ReportSummary([
                new ClassNode(__FILE__, [], self::class, 1),
            ]),
            'tests/Console/Reporter/TextReporterTest.php

<error>The hunt is over! 1 unused classes detected.</error>

',
        ];

        yield [
            new ReportSummary([
                new ClassNode(__FILE__, [], self::class, 1),
                new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 1),
                new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 1),
            ]),
            'tests/Console/Reporter/TextReporterTest.php
tests/Console/Reporter/TestCase.php
tests/Console/Reporter/Assert.php

<error>The hunt is over! 3 unused classes detected.</error>

',
        ];
    }
}
