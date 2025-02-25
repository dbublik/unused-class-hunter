<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\ValueObject;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReaderResult::class)]
final class ReaderResultTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $config = new Config();
        $fileNodes = [];

        $readerResult = new ReaderResult($config, $fileNodes);

        self::assertSame($config, $readerResult->getConfig());
        self::assertEmpty($readerResult->getUsedClasses());
        self::assertEmpty($readerResult->getUnusedClasses());
        self::assertEmpty($readerResult->getUsedFilesByName(self::class));
        self::assertNull($readerResult->getClassByName(self::class));
    }

    public function testGetUsedClasses(): void
    {
        $config = new Config();
        $fileNodes = [
            new FileNode('file1.txt', [Assert::class]),
            new FileNode('file2.txt', [Assert::class, Reorderable::class]),
            new ClassNode('class1.txt', [Config::class], self::class, 1),
            new ClassNode('class2.txt', [ReaderResult::class], TestCase::class, 5),
            new ClassNode('class3.txt', [ReaderResult::class], Reorderable::class, 3),
        ];

        $readerResult = new ReaderResult($config, $fileNodes);

        self::assertSame(
            [Reorderable::class],
            array_keys($readerResult->getUsedClasses())
        );
    }

    public function testGetUnusedClasses(): void
    {
        $config = new Config();
        $fileNodes = [
            new FileNode('file1.txt', [Assert::class]),
            new FileNode('file2.txt', [Assert::class, Reorderable::class]),
            new ClassNode('class1.txt', [Config::class], self::class, 1),
            new ClassNode('class2.txt', [ReaderResult::class], TestCase::class, 5),
            new ClassNode('class3.txt', [ReaderResult::class], Reorderable::class, 3),
        ];

        $readerResult = new ReaderResult($config, $fileNodes);

        self::assertSame(
            [
                self::class,
                TestCase::class,
            ],
            array_keys($readerResult->getUnusedClasses())
        );
    }

    public function testUnusedClass(): void
    {
        $config = new Config();
        $fileNode = new FileNode('file.txt', [self::class]);
        $classNode = new ClassNode('class.txt', [], self::class, 1);
        $readerResult = new ReaderResult($config, [$fileNode, $classNode]);

        $readerResult->unusedClass($classNode);

        self::assertSame(
            ['file.txt'],
            array_map(
                static fn (AbstractFileNode $fileNode): string => $fileNode->getFile(),
                $readerResult->getUsedFilesByName(self::class),
            )
        );
        self::assertSame(
            [self::class],
            array_keys($readerResult->getUnusedClasses())
        );
    }

    public function testGetUsedFilesByName(): void
    {
        $config = new Config();
        $fileNodes = [
            new FileNode('file1.txt', [Assert::class]),
            new FileNode('file2.txt', [Assert::class, Reorderable::class]),
            new ClassNode('class1.txt', [Config::class], self::class, 1),
            new ClassNode('class2.txt', [ReaderResult::class], TestCase::class, 5),
            new ClassNode('class3.txt', [ReaderResult::class], Reorderable::class, 3),
        ];

        $readerResult = new ReaderResult($config, $fileNodes);

        self::assertSame(
            ['file1.txt', 'file2.txt'],
            array_map(
                static fn (AbstractFileNode $fileNode): string => $fileNode->getFile(),
                $readerResult->getUsedFilesByName(Assert::class),
            )
        );
        self::assertSame(
            ['file2.txt'],
            array_map(
                static fn (AbstractFileNode $fileNode): string => $fileNode->getFile(),
                $readerResult->getUsedFilesByName(Reorderable::class),
            )
        );
        self::assertSame(
            ['class1.txt'],
            array_map(
                static fn (AbstractFileNode $fileNode): string => $fileNode->getFile(),
                $readerResult->getUsedFilesByName(Config::class),
            )
        );
        self::assertSame(
            ['class2.txt', 'class3.txt'],
            array_map(
                static fn (AbstractFileNode $fileNode): string => $fileNode->getFile(),
                $readerResult->getUsedFilesByName(ReaderResult::class),
            )
        );
        self::assertEmpty(
            array_map(
                static fn (AbstractFileNode $fileNode): string => $fileNode->getFile(),
                $readerResult->getUsedFilesByName(self::class),
            )
        );
    }

    public function testGetClassByName(): void
    {
        $config = new Config();
        $fileNodes = [
            new FileNode('file1.txt', [Assert::class]),
            new FileNode('file2.txt', [Assert::class, Reorderable::class]),
            new ClassNode('class1.txt', [Config::class], self::class, 1),
            new ClassNode('class2.txt', [ReaderResult::class], TestCase::class, 5),
            new ClassNode('class3.txt', [ReaderResult::class], Reorderable::class, 3),
        ];

        $readerResult = new ReaderResult($config, $fileNodes);

        self::assertSame('class1.txt', $readerResult->getClassByName(self::class)?->getFile());
        self::assertSame('class2.txt', $readerResult->getClassByName(TestCase::class)?->getFile());
        self::assertSame('class3.txt', $readerResult->getClassByName(Reorderable::class)?->getFile());
        self::assertNull($readerResult->getClassByName(Config::class));
        self::assertNull($readerResult->getClassByName(ReaderResult::class));
        self::assertNull($readerResult->getClassByName(Assert::class));
    }

    public function testGetClassByNameInternalOrder(): void
    {
        $config = new Config();
        $class1 = new ClassNode('class1.txt', [], self::class, 1);
        $class2 = new ClassNode('class2.txt', [], self::class, 1);

        $readerResult = new ReaderResult($config, []);

        (new \ReflectionProperty(ReaderResult::class, 'usedClasses'))
            ->setValue($readerResult, [$class1->getName() => $class1]);
        (new \ReflectionProperty(ReaderResult::class, 'unusedClasses'))
            ->setValue($readerResult, [$class2->getName() => $class2]);

        self::assertSame('class1.txt', $readerResult->getClassByName(self::class)?->getFile());
    }
}
