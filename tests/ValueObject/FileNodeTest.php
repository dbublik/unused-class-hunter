<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\ValueObject;

use DBublik\UnusedClassHunter\ValueObject\FileNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FileNode::class)]
final class FileNodeTest extends TestCase
{
    /**
     * @param non-empty-string $file
     * @param list<class-string> $usedClasses
     */
    #[DataProvider('provideConstructor')]
    public function testConstructor(string $file, array $usedClasses): void
    {
        $fileNode = new FileNode($file, $usedClasses);

        self::assertSame($file, $fileNode->getFile());
        self::assertSame($usedClasses, $fileNode->getUsedClasses());
    }

    /**
     * @return iterable<array{0: non-empty-string, 1: list<class-string>}>
     */
    public static function provideConstructor(): iterable
    {
        yield [
            'file',
            [],
        ];

        yield [
            'file',
            [self::class],
        ];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideConstructorException')]
    public function testConstructorException(string $exceptionMessage, string $file, array $usedClasses): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        // @phpstan-ignore argument.type, argument.type
        new FileNode($file, $usedClasses);
    }

    /**
     * @return iterable<array{0: string, 1: string, 2: array}>
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function provideConstructorException(): iterable
    {
        yield [
            'Value must be a non empty string',
            '',
            [],
        ];

        yield [
            'Array must have a list of strings',
            'file',
            ['key' => 'value'],
        ];

        yield [
            'Value must be a string, got "integer"',
            'file',
            [1],
        ];
    }

    /**
     * @param non-empty-string $file
     * @param list<class-string> $usedClasses
     */
    #[DataProvider('provideJsonSerialize')]
    public function testJsonSerialize(string $file, array $usedClasses, string $expectedJson): void
    {
        $fileNode = new FileNode($file, $usedClasses);

        $json = json_encode($fileNode, JSON_THROW_ON_ERROR);

        self::assertSame($expectedJson, $json);
    }

    /**
     * @return iterable<array{0: non-empty-string, 1: list<class-string>, 2: string}>
     */
    public static function provideJsonSerialize(): iterable
    {
        yield [
            'file.php',
            [],
            '{"file":"file.php","usedClasses":[]}',
        ];

        yield [
            'file.php',
            [self::class],
            '{"file":"file.php","usedClasses":["' . addcslashes(self::class, '\\') . '"]}',
        ];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideFromData')]
    public function testFromData(bool $isValid, array $data): void
    {
        $fileNode = FileNode::fromData($data);

        self::assertSame($isValid, null !== $fileNode);
    }

    /**
     * @return iterable<array{0: bool, 1: array}>
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function provideFromData(): iterable
    {
        yield [
            false,
            [],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => '',
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [1],
            ],
        ];

        yield [
            true,
            [
                'file' => 'file.php',
                'usedClasses' => [],
            ],
        ];

        yield [
            true,
            [
                'file' => 'file.php',
                'usedClasses' => [self::class],
            ],
        ];
    }
}
