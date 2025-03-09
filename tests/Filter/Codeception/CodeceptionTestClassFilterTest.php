<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Filter\Codeception;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\Codeception\CodeceptionTestClassFilter;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CodeceptionTestClassFilter::class)]
final class CodeceptionTestClassFilterTest extends TestCase
{
    /**
     * @param class-string $name
     * @param list<class-string> $extends
     */
    #[DataProvider('provideIsIgnored')]
    public function testIsIgnored(bool $expectedIsIgnored, string $name, array $extends): void
    {
        $filter = new CodeceptionTestClassFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: $name,
            startLine: 1,
            extends: $extends,
        );
        $readerResult = new ReaderResult(new Config(), []);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($expectedIsIgnored, $isIgnored);
    }

    /**
     * @return iterable<
     *     array{
     *         expectedIsIgnored: bool,
     *         name: class-string,
     *         extends: list<class-string>
     *     }
     * >
     */
    public static function provideIsIgnored(): iterable
    {
        yield [
            'expectedIsIgnored' => false,
            'name' => self::class,
            'extends' => [],
        ];

        // @phpstan-ignore generator.valueType
        yield [
            'expectedIsIgnored' => true,
            'name' => 'ExampleCest',
            'extends' => [],
        ];

        // @phpstan-ignore generator.valueType
        yield [
            'expectedIsIgnored' => false,
            'name' => 'ExampleCestEnd',
            'extends' => [],
        ];

        // @phpstan-ignore generator.valueType
        yield [
            'expectedIsIgnored' => false,
            'name' => 'ExampleCest',
            'extends' => [self::class],
        ];

        // @phpstan-ignore generator.valueType
        yield [
            'expectedIsIgnored' => true,
            'name' => 'ExampleCest',
            'extends' => ['AbstractExampleCest'],
        ];

        // @phpstan-ignore generator.valueType
        yield [
            'expectedIsIgnored' => false,
            'name' => 'ExampleCest',
            'extends' => ['AbstractExampleCestEnd'],
        ];

        // @phpstan-ignore generator.valueType
        yield [
            'expectedIsIgnored' => false,
            'name' => 'ExampleCest',
            'extends' => ['AbstractExampleCest', self::class],
        ];
    }
}
