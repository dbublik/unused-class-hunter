<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ClassFilter::class)]
final class ClassFilterTest extends TestCase
{
    /**
     * @param class-string $class
     * @param list<class-string> $ignoredClasses
     */
    #[DataProvider('provideIsIgnored')]
    public function testIsIgnored(bool $expectedIsIgnored, string $class, array $ignoredClasses): void
    {
        $filter = new ClassFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: $class,
            startLine: 1,
        );
        $config = (new Config())->withIgnoredClasses(...$ignoredClasses);
        $readerResult = new ReaderResult($config, []);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($expectedIsIgnored, $isIgnored);
    }

    /**
     * @return iterable<
     *     array{
     *         expectedIsIgnored: bool,
     *         class: class-string,
     *         ignoredClasses: list<class-string>
     *     }
     * >
     */
    public static function provideIsIgnored(): iterable
    {
        yield [
            'expectedIsIgnored' => false,
            'class' => self::class,
            'ignoredClasses' => [],
        ];

        yield [
            'expectedIsIgnored' => true,
            'class' => self::class,
            'ignoredClasses' => [self::class],
        ];

        yield [
            'expectedIsIgnored' => true,
            'class' => self::class,
            'ignoredClasses' => [TestCase::class],
        ];

        yield [
            'expectedIsIgnored' => false,
            'class' => TestCase::class,
            'ignoredClasses' => [self::class],
        ];
    }
}
