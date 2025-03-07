<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

use DBublik\UnusedClassHunter\Console\Reporter\GithubReporter;
use DBublik\UnusedClassHunter\Console\Reporter\GitlabReporter;
use DBublik\UnusedClassHunter\Console\Reporter\ReporterInterface;
use DBublik\UnusedClassHunter\Console\Reporter\ReportFactory;
use DBublik\UnusedClassHunter\Console\Reporter\TextReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportFactory::class)]
final class ReportFactoryTest extends TestCase
{
    public function testConstructor(): void
    {
        $factory = new ReportFactory();

        /** @var list<ReporterInterface> $reporters */
        $reporters = (new \ReflectionProperty(ReportFactory::class, 'reporters'))->getValue($factory);
        self::assertSame(
            [
                TextReporter::class,
                GitlabReporter::class,
                GithubReporter::class,
            ],
            array_map(
                static fn (ReporterInterface $reporter): string => $reporter::class,
                $reporters
            )
        );
    }

    #[DataProvider('provideGetReporterException')]
    public function testGetReporterException(string $format): void
    {
        $factory = new ReportFactory();

        $this->expectExceptionObject(
            new \InvalidArgumentException('Unsupported format.'),
        );

        // @phpstan-ignore argument.type
        $factory->getReporter($format);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public static function provideGetReporterException(): iterable
    {
        yield [
            '',
        ];

        yield [
            'bad_format',
        ];
    }

    /**
     * @param non-empty-string $format
     */
    #[DataProvider('provideGetReporter')]
    public function testGetReporter(string $format, ReporterInterface $expectedReporter): void
    {
        $factory = new ReportFactory();

        $reporter = $factory->getReporter($format);

        self::assertSame($expectedReporter::class, $reporter::class);
    }

    /**
     * @return iterable<array{0: non-empty-string, 1: ReporterInterface}>
     */
    public static function provideGetReporter(): iterable
    {
        yield [
            'text',
            new TextReporter(),
        ];

        yield [
            'gitlab',
            new GitlabReporter(),
        ];

        yield [
            'github',
            new GithubReporter(),
        ];
    }
}
