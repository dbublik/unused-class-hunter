<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\ValueObject;

use DBublik\UnusedClassHunter\Config;
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
        self::assertNull($readerResult->getClassByName(self::class));
        self::assertEmpty(iterator_to_array($readerResult->getUnusedClasses()));
    }

    public function testConstructor(): void
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
        self::assertNotEmpty($unusedClasses = iterator_to_array($readerResult->getUnusedClasses()));
        self::assertSame(
            [
                self::class,
                TestCase::class,
            ],
            array_map(
                static fn (ClassNode $classNode): string => $classNode->getName(),
                $unusedClasses,
            )
        );
    }
}
