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
            new ReportSummary(unusedClasses: [], duration: 1.203, memory: 13_000_000),
            '<info>Success! The hunt is over — no unused classes found.</info>

Duration 1.203 seconds, 12.40 MB memory used.

',
        ];

        yield [
            new ReportSummary(unusedClasses: [], duration: 1.203, memory: 13_000_000, isDecoratedOutput: false),
            'Success! The hunt is over — no unused classes found.

Duration 1.203 seconds, 12.40 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                ],
                duration: 12.045,
                memory: 262_200_000,
            ),
            '<comment>tests/Console/Reporter/TextReporterTest.php</comment>

<error>The hunt is over! 1 unused classes detected.</error>

Duration 12.045 seconds, 250.05 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                ],
                duration: 12.045,
                memory: 262_200_000,
                isDeletable: true,
            ),
            '[-] tests/Console/Reporter/TextReporterTest.php

<error>The hunt is over! 1 unused classes deleted.</error>

Duration 12.045 seconds, 250.05 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                ],
                duration: 12.045,
                memory: 262_200_000,
                isDecoratedOutput: false,
            ),
            'tests/Console/Reporter/TextReporterTest.php

The hunt is over! 1 unused classes detected.

Duration 12.045 seconds, 250.05 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                ],
                duration: 12.045,
                memory: 262_200_000,
                isDeletable: true,
                isDecoratedOutput: false,
            ),
            '[-] tests/Console/Reporter/TextReporterTest.php

The hunt is over! 1 unused classes deleted.

Duration 12.045 seconds, 250.05 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                    new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 1),
                    new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 1),
                ],
                duration: 3.920,
                memory: 90_000_000,
            ),
            '<comment>tests/Console/Reporter/TextReporterTest.php</comment>
<comment>tests/Console/Reporter/TestCase.php</comment>
<comment>tests/Console/Reporter/Assert.php</comment>

<error>The hunt is over! 3 unused classes detected.</error>

Duration 3.920 seconds, 85.83 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                    new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 1),
                    new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 1),
                ],
                duration: 3.920,
                memory: 90_000_000,
                isDeletable: true,
            ),
            '[-] tests/Console/Reporter/TextReporterTest.php
[-] tests/Console/Reporter/TestCase.php
[-] tests/Console/Reporter/Assert.php

<error>The hunt is over! 3 unused classes deleted.</error>

Duration 3.920 seconds, 85.83 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                    new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 1),
                    new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 1),
                ],
                duration: 3.920,
                memory: 90_000_000,
                isDecoratedOutput: false,
            ),
            'tests/Console/Reporter/TextReporterTest.php
tests/Console/Reporter/TestCase.php
tests/Console/Reporter/Assert.php

The hunt is over! 3 unused classes detected.

Duration 3.920 seconds, 85.83 MB memory used.

',
        ];

        yield [
            new ReportSummary(
                unusedClasses: [
                    new ClassNode(__FILE__, [], self::class, 1),
                    new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 1),
                    new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 1),
                ],
                duration: 3.920,
                memory: 90_000_000,
                isDeletable: true,
                isDecoratedOutput: false,
            ),
            '[-] tests/Console/Reporter/TextReporterTest.php
[-] tests/Console/Reporter/TestCase.php
[-] tests/Console/Reporter/Assert.php

The hunt is over! 3 unused classes deleted.

Duration 3.920 seconds, 85.83 MB memory used.

',
        ];
    }
}
