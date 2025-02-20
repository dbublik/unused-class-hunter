<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console\Reporter;

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

    /**
     * @param list<ClassNode> $unusedClasses
     */
    #[DataProvider('provideGenerate')]
    public function testGenerate(array $unusedClasses, string $expectedReport): void
    {
        $reporter = new TextReporter();

        $report = $reporter->generate($unusedClasses);

        self::assertSame($expectedReport, $report);
    }

    /**
     * @return iterable<array{0: list<ClassNode>, 1: string}>
     */
    public static function provideGenerate(): iterable
    {
        yield [
            [],
            '<info>Success! The hunt is over — no unused classes found.</info>

',
        ];

        yield [
            [
                new ClassNode(__FILE__, [], self::class, 1),
            ],
            'tests/Console/Reporter/TextReporterTest.php

<error>The hunt is over! 1 unused classes detected.</error>

',
        ];

        yield [
            [
                new ClassNode(__FILE__, [], self::class, 1),
                new ClassNode(__DIR__ . '/TestCase.php', [], TestCase::class, 1),
                new ClassNode(__DIR__ . '/Assert.php', [], Assert::class, 1),
            ],
            'tests/Console/Reporter/TextReporterTest.php
tests/Console/Reporter/TestCase.php
tests/Console/Reporter/Assert.php

<error>The hunt is over! 3 unused classes detected.</error>

',
        ];
    }
}
