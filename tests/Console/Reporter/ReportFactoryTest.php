<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

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

        /** @var ReporterInterface[] $reporters */
        $reporters = (new \ReflectionProperty(ReportFactory::class, 'reporters'))->getValue($factory);
        self::assertSame(
            [
                TextReporter::class,
                GitlabReporter::class,
            ],
            array_map(
                static fn (ReporterInterface $reporter): string => $reporter::class,
                $reporters
            )
        );
    }

    #[DataProvider('provideGetReporter')]
    public function getReporter(string $format, ?ReporterInterface $expectedReporter): void
    {
        $factory = new ReportFactory();

        if (null === $expectedReporter) {
            $this->expectExceptionObject(
                new \InvalidArgumentException('Unsupported format.'),
            );
        }

        $reporter = $factory->getReporter($format);

        if (null !== $expectedReporter) {
            self::assertSame($expectedReporter::class, $reporter::class);
        }
    }

    /**
     * @return iterable<array{0: string, 1: null|ReporterInterface}>
     */
    public static function provideGetReporter(): iterable
    {
        yield [
            '',
            null,
        ];

        yield [
            'bad_format',
            null,
        ];

        yield [
            'text',
            new TextReporter(),
        ];

        yield [
            'gitlab',
            new GitlabReporter(),
        ];
    }
}
