<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Filter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AsAliasAttributeFilter;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AsAliasAttributeFilter::class)]
final class AsAliasAttributeFilterTest extends TestCase
{
    /**
     * @param list<class-string> $attributes
     */
    #[DataProvider('provideIsIgnoredHasAttribute')]
    public function testIsIgnoredHasAttribute(bool $expectedIsIgnored, array $attributes): void
    {
        $filter = new AsAliasAttributeFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            extends: [TestCase::class],
            attributes: $attributes,
        );
        $extendNode = new ClassNode(
            file: 'file2.txt',
            usedClasses: [TestCase::class],
            name: Assert::class,
            startLine: 1,
        );
        $readerResult = new ReaderResult(new Config(), [$extendNode]);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($expectedIsIgnored, $isIgnored);
    }

    /**
     * @return iterable<
     *     array{
     *         expectedIsIgnored: bool,
     *         attributes: list<class-string>
     *     }
     * >
     */
    public static function provideIsIgnoredHasAttribute(): iterable
    {
        yield [
            'expectedIsIgnored' => false,
            'attributes' => [],
        ];

        yield [
            'expectedIsIgnored' => false,
            'attributes' => [self::class],
        ];

        yield [
            'expectedIsIgnored' => true,
            'attributes' => [self::getAsAliasAttribute()],
        ];
    }

    /**
     * @param list<class-string> $extends
     * @param list<class-string> $implements
     * @param list<AbstractFileNode> $fileNodes
     */
    #[DataProvider('provideIsIgnoredHasParentAttribute')]
    public function testIsIgnoredHasParentAttribute(
        bool $expectedIsIgnored,
        array $extends,
        array $implements,
        array $fileNodes,
    ): void {
        $filter = new AsAliasAttributeFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            extends: $extends,
            implements: $implements,
            attributes: [self::getAsAliasAttribute()],
        );
        $readerResult = new ReaderResult(new Config(), $fileNodes);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($expectedIsIgnored, $isIgnored);
    }

    /**
     * @return iterable<
     *     array{
     *         expectedIsIgnored: bool,
     *         extends: list<class-string>,
     *         implements: list<class-string>,
     *         fileNodes: list<AbstractFileNode>
     *     }
     * >
     */
    public static function provideIsIgnoredHasParentAttribute(): iterable
    {
        yield [
            'expectedIsIgnored' => false,
            'extends' => [],
            'implements' => [],
            'fileNodes' => [],
        ];

        yield [
            'expectedIsIgnored' => false,
            'extends' => [TestCase::class],
            'implements' => [],
            'fileNodes' => [],
        ];

        yield [
            'expectedIsIgnored' => false,
            'extends' => [TestCase::class],
            'implements' => [self::class],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                    name: self::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => false,
            'extends' => [TestCase::class],
            'implements' => [],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                    name: self::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => false,
            'extends' => [TestCase::class],
            'implements' => [TestCase::class],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                    name: Assert::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => true,
            'extends' => [TestCase::class],
            'implements' => [],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                    name: Assert::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => true,
            'extends' => [TestCase::class],
            'implements' => [],
            'fileNodes' => [
                new FileNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => true,
            'extends' => [TestCase::class],
            'implements' => [],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                    name: Assert::class,
                    startLine: 1,
                ),
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [TestCase::class],
                    name: Reorderable::class,
                    startLine: 1,
                ),
            ],
        ];
    }

    /**
     * @return class-string
     */
    private static function getAsAliasAttribute(): string
    {
        // @phpstan-ignore return.type
        return 'Symfony\Component\DependencyInjection\Attribute\AsAlias';
    }
}
