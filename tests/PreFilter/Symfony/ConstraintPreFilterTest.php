<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\PreFilter\Symfony;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\PreFilter\Symfony\ConstraintPreFilter;
use DBublik\UnusedClassHunter\Tests\Fixtures\PreFilter\ConstraintPreFilter\ExampleConstraint;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConstraintPreFilter::class)]
final class ConstraintPreFilterTest extends TestCase
{
    public function testIsUnusedNotConstraint(): void
    {
        $preFilter = new ConstraintPreFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
        );
        $readerResult = new ReaderResult(new Config(), []);

        $isUnused = $preFilter->isUnused($classNode, $readerResult);

        self::assertFalse($isUnused);
    }

    /**
     * @param list<AbstractFileNode> $fileNodes
     */
    #[DataProvider('provideIsUnusedFalse')]
    public function testIsUnusedFalse(array $fileNodes): void
    {
        $preFilter = new ConstraintPreFilter();
        $constraintNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: ExampleConstraint::class,
            startLine: 1,
        );
        $readerResult = new ReaderResult(new Config(), $fileNodes);

        $isUnused = $preFilter->isUnused($constraintNode, $readerResult);

        self::assertFalse($isUnused);
    }

    /**
     * @return iterable<array{0: list<AbstractFileNode>}>
     */
    public static function provideIsUnusedFalse(): iterable
    {
        yield [
            [],
        ];

        yield [
            [
                new FileNode(
                    file: 'file.txt',
                    usedClasses: [ExampleConstraint::class],
                ),
            ],
        ];

        yield [
            [
                new ClassNode(
                    file: 'file.txt',
                    usedClasses: [],
                    name: self::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            [
                new ClassNode(
                    file: 'file.txt',
                    usedClasses: [ExampleConstraint::class],
                    name: self::class,
                    startLine: 1,
                ),
                new FileNode(
                    file: 'file.txt',
                    usedClasses: [ExampleConstraint::class],
                ),
            ],
        ];
    }

    public function testIsUnused(): void
    {
        $preFilter = new ConstraintPreFilter();
        $constraintNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: ExampleConstraint::class,
            startLine: 1,
        );
        $validatorNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [ExampleConstraint::class],
            // @phpstan-ignore argument.type
            name: ExampleConstraint::class . 'Validator',
            startLine: 1,
        );
        $readerResult = new ReaderResult(new Config(), [$validatorNode]);

        $isUnused = $preFilter->isUnused($constraintNode, $readerResult);

        self::assertTrue($isUnused);
    }
}
