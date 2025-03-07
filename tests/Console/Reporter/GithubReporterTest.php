<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

use DBublik\UnusedClassHunter\Console\Reporter\GithubReporter;
use DBublik\UnusedClassHunter\Console\Reporter\ReportSummary;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GithubReporter::class)]
final class GithubReporterTest extends TestCase
{
    public function testGetFormat(): void
    {
        $reporter = new GithubReporter();

        self::assertSame('github', $reporter->getFormat());
    }

    #[DataProvider('provideGenerate')]
    public function testGenerate(ReportSummary $reportSummary, string $expectedReport): void
    {
        $reporter = new GithubReporter();

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
            self::getReport(),
        ];

        yield [
            new ReportSummary(
                unusedClasses: [new ClassNode(__FILE__, [], self::class, 1)],
                isDecoratedOutput: true,
            ),
            self::getReport(
                self::getReportLine(__FILE__, self::class, 1)
            ),
        ];

        yield [
            new ReportSummary(
                unusedClasses: [new ClassNode(__FILE__, [], self::class, 1)],
                isDecoratedOutput: false,
            ),
            self::getReport(
                self::getReportLine(__FILE__, self::class, 1)
            ),
        ];

        yield [
            new ReportSummary(
                unusedClasses: [new ClassNode(__FILE__, [], self::class, 1)],
                isDeletable: true,
            ),
            self::getReport(
                self::getReportLine(__FILE__, self::class, 1, true)
            ),
        ];

        yield [
            new ReportSummary([
                new ClassNode(__FILE__, [], self::class, 11),
                new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 5),
                new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 9),
            ]),
            self::getReport(
                self::getReportLine(__FILE__, self::class, 11),
                self::getReportLine(__DIR__ . '/TestCase.php', TestCase::class, 5),
                self::getReportLine(__DIR__ . '/Assert.php', Assert::class, 9)
            ),
        ];
    }

    private static function getReport(string ...$lines): string
    {
        return '::group::Hunter report' . PHP_EOL . implode('', $lines) . '::endgroup::' . PHP_EOL;
    }

    private static function getReportLine(string $file, string $name, int $startLine, bool $isDeletable = false): string
    {
        $report = \sprintf(
            '::error file=%s,line=%d::The %s class is not used%s',
            $file,
            $startLine,
            $name,
            $isDeletable ? ' and deleted' : ''
        );

        return $report . PHP_EOL;
    }
}
