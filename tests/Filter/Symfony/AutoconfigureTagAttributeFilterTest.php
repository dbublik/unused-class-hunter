<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Filter\Symfony;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\Symfony\AutoconfigureTagAttributeFilter;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AutoconfigureTagAttributeFilter::class)]
final class AutoconfigureTagAttributeFilterTest extends TestCase
{
    /**
     * @param list<class-string> $attributes
     */
    #[DataProvider('provideIsIgnoredHasAttribute')]
    public function testIsIgnoredHasAttribute(bool $expectedIsIgnored, array $attributes): void
    {
        $filter = new AutoconfigureTagAttributeFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            attributes: $attributes,
        );
        $readerResult = new ReaderResult(new Config(), []);

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
            'attributes' => [self::getAutoconfigureTagAttribute()],
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
        $filter = new AutoconfigureTagAttributeFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            extends: $extends,
            implements: $implements,
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
            'extends' => [TestCase::class],
            'implements' => [],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [],
                    name: TestCase::class,
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
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    attributes: [self::getAutoconfigureTagAttribute()],
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => true,
            'extends' => [Assert::class, TestCase::class],
            'implements' => [],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [],
                    name: Test::class,
                    startLine: 1,
                    attributes: [],
                ),
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    attributes: [self::getAutoconfigureTagAttribute()],
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => false,
            'extends' => [],
            'implements' => [TestCase::class],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            'expectedIsIgnored' => true,
            'extends' => [],
            'implements' => [TestCase::class],
            'fileNodes' => [
                new ClassNode(
                    file: 'test.txt',
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    attributes: [self::getAutoconfigureTagAttribute()],
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
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    extends: [Assert::class],
                ),
                new ClassNode(
                    file: 'assert.txt',
                    usedClasses: [],
                    name: Assert::class,
                    startLine: 1,
                    extends: [],
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
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    extends: [Assert::class],
                ),
                new ClassNode(
                    file: 'assert.txt',
                    usedClasses: [],
                    name: Assert::class,
                    startLine: 1,
                    attributes: [self::getAutoconfigureTagAttribute()],
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
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    implements: [Assert::class],
                ),
                new ClassNode(
                    file: 'reorderable.txt',
                    usedClasses: [],
                    name: Reorderable::class,
                    startLine: 1,
                    attributes: [self::getAutoconfigureTagAttribute()],
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
                    usedClasses: [],
                    name: TestCase::class,
                    startLine: 1,
                    implements: [Reorderable::class],
                ),
                new ClassNode(
                    file: 'reorderable.txt',
                    usedClasses: [],
                    name: Reorderable::class,
                    startLine: 1,
                    attributes: [self::getAutoconfigureTagAttribute()],
                ),
            ],
        ];
    }

    #[DataProvider('provideIsIgnoredMaxDeep')]
    public function testIsIgnoredMaxDeep(bool $expectedIsIgnored, int $maxDeep): void
    {
        $filter = new AutoconfigureTagAttributeFilter(maxDeep: $maxDeep);
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            extends: [TestCase::class],
        );
        $readerResult = new ReaderResult(new Config(), [
            new ClassNode(
                file: 'test.txt',
                usedClasses: [],
                name: TestCase::class,
                startLine: 1,
                implements: [Reorderable::class],
            ),
            new ClassNode(
                file: 'reorderable.txt',
                usedClasses: [],
                name: Reorderable::class,
                startLine: 1,
                attributes: [self::getAutoconfigureTagAttribute()],
            ),
        ]);

        $isIgnored = $filter->isIgnored($classNode, $readerResult);

        self::assertSame($expectedIsIgnored, $isIgnored);
    }

    /**
     * @return iterable<
     *     array{
     *         expectedIsIgnored: bool,
     *         maxDeep: int
     *     }
     * >
     */
    public static function provideIsIgnoredMaxDeep(): iterable
    {
        yield [
            'expectedIsIgnored' => false,
            'maxDeep' => 1,
        ];

        yield [
            'expectedIsIgnored' => true,
            'maxDeep' => 2,
        ];
    }

    /**
     * @return class-string
     */
    private static function getAutoconfigureTagAttribute(): string
    {
        // @phpstan-ignore return.type
        return 'Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag';
    }
}
