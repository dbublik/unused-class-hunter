<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\PreFilter;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\PreFilter\ServiceEntityRepositoryPreFilter;
use DBublik\UnusedClassHunter\Tests\Fixtures\PreFilter\ServiceEntityRepositoryPreFilter\ExampleRepository;
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
#[CoversClass(ServiceEntityRepositoryPreFilter::class)]
final class ServiceEntityRepositoryPreFilterTest extends TestCase
{
    public function testIsUnusedNotRepository(): void
    {
        $preFilter = new ServiceEntityRepositoryPreFilter();
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
        $preFilter = new ServiceEntityRepositoryPreFilter();
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: ExampleRepository::class,
            startLine: 1,
        );
        $readerResult = new ReaderResult(new Config(), $fileNodes);

        $isUnused = $preFilter->isUnused($classNode, $readerResult);

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
                    usedClasses: [ExampleRepository::class],
                ),
            ],
        ];

        yield [
            [
                new ClassNode(
                    file: 'file.txt',
                    usedClasses: [ExampleRepository::class],
                    name: self::class,
                    startLine: 1,
                ),
            ],
        ];

        yield [
            [
                new FileNode(
                    file: 'file.txt',
                    usedClasses: [ExampleRepository::class],
                ),
                new ClassNode(
                    file: 'file.txt',
                    usedClasses: [ExampleRepository::class],
                    name: self::class,
                    startLine: 1,
                ),
            ],
        ];
    }

    public function testIsUnused(): void
    {
        $preFilter = new ServiceEntityRepositoryPreFilter();
        $repositoryNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: ExampleRepository::class,
            startLine: 1,
        );
        $entityNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [ExampleRepository::class],
            name: self::class,
            startLine: 1,
            // @phpstan-ignore argument.type
            attributes: ['Doctrine\ORM\Mapping\Table'],
        );
        $readerResult = new ReaderResult(new Config(), [$entityNode]);

        $isUnused = $preFilter->isUnused($repositoryNode, $readerResult);

        self::assertTrue($isUnused);
    }
}
