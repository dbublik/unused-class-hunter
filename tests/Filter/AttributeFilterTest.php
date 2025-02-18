<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AttributeFilter::class)]
final class AttributeFilterTest extends TestCase
{
    /**
     * @param list<class-string> $attributes
     * @param list<class-string> $ignoredAttributes
     */
    #[DataProvider('provideIsIgnored')]
    public function testIsIgnored(bool $expectedIsIgnored, array $attributes, array $ignoredAttributes): void
    {
        $filter = new AttributeFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            attributes: $attributes,
        );
        $config = (new Config())->withIgnoredAttributes(...$ignoredAttributes);
        $readerResult = new ReaderResult($config, []);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($expectedIsIgnored, $isIgnored);
    }

    /**
     * @return iterable<
     *     array{
     *         expectedIsIgnored: bool,
     *         attributes: list<class-string>,
     *         ignoredAttributes: list<class-string>
     *     }
     * >
     */
    public static function provideIsIgnored(): iterable
    {
        yield [
            'expectedIsIgnored' => false,
            'attributes' => [],
            'ignoredAttributes' => [],
        ];

        yield [
            'expectedIsIgnored' => false,
            'attributes' => [self::class],
            'ignoredAttributes' => [],
        ];

        yield [
            'expectedIsIgnored' => false,
            'attributes' => [],
            'ignoredAttributes' => [self::class],
        ];

        yield [
            'expectedIsIgnored' => true,
            'attributes' => [self::class],
            'ignoredAttributes' => [self::class],
        ];

        yield [
            'expectedIsIgnored' => true,
            'attributes' => [self::class],
            'ignoredAttributes' => [TestCase::class],
        ];

        yield [
            'expectedIsIgnored' => false,
            'attributes' => [TestCase::class],
            'ignoredAttributes' => [self::class],
        ];
    }
}
