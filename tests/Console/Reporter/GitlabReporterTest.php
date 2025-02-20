<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

use DBublik\UnusedClassHunter\Console\Reporter\GitlabReporter;
use DBublik\UnusedClassHunter\Console\Reporter\ReportSummary;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GitlabReporter::class)]
final class GitlabReporterTest extends TestCase
{
    public function testGetFormat(): void
    {
        $reporter = new GitlabReporter();

        self::assertSame('gitlab', $reporter->getFormat());
    }

    #[DataProvider('provideGenerate')]
    public function testGenerate(ReportSummary $reportSummary, string $expectedReport): void
    {
        $reporter = new GitlabReporter();

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
            '[]',
        ];

        yield [
            new ReportSummary([
                new ClassNode(__FILE__, [], self::class, 1),
            ]),
            \sprintf(
                '[%s]',
                self::getReportLine(__FILE__, self::class, 1)
            ),
        ];

        yield [
            new ReportSummary([
                new ClassNode(__FILE__, [], self::class, 11),
                new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 5),
                new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 9),
            ]),
            \sprintf(
                '[%s,%s,%s]',
                self::getReportLine(__FILE__, self::class, 11),
                self::getReportLine(__DIR__ . '/TestCase.php', TestCase::class, 5),
                self::getReportLine(__DIR__ . '/Assert.php', Assert::class, 9)
            ),
        ];
    }

    private static function getReportLine(string $file, string $name, int $startLine): string
    {
        $report = \sprintf(
            '{"description":"The %s class is not used.","fingerprint":"%s","severity":"minor","location":{"path":"%s","lines":{"begin":%d}}}',
            $name,
            md5($file),
            $file,
            $startLine,
        );

        return addcslashes($report, '\/');
    }
}
