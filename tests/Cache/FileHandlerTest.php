<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Cache;

use DBublik\UnusedClassHunter\Cache\FileHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FileHandler::class)]
final class FileHandlerTest extends TestCase
{
    public function testConstructor(): void
    {
        $file = __DIR__ . '/../Fixtures/Not/Found';

        $handler = new FileHandler($file);

        self::assertSame($file, $handler->getName());
    }

    /**
     * @param non-empty-string $file
     *
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideRead')]
    public function testRead(string $file, ?array $expectedData): void
    {
        $handler = new FileHandler($file);

        $data = $handler->read();

        self::assertSame($expectedData, $data);
    }

    /**
     * @return iterable<array{0: string, 1: null|array}>
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function provideRead(): iterable
    {
        yield [
            __DIR__ . '/../Fixtures/Not/Found',
            null,
        ];

        yield [
            __DIR__ . '/../Fixtures/Cache/FileHandler/not-json.txt',
            null,
        ];

        yield [
            __DIR__ . '/../Fixtures/Cache/FileHandler/bad-json.txt',
            null,
        ];

        yield [
            __DIR__ . '/../Fixtures/Cache/FileHandler/good-json.txt',
            ['key' => 'value', 'some_key' => 123, 'other_key' => [1, 6, '2']],
        ];
    }

    public function testWrite(): void
    {
        $file = sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true);
        $fileHandler = new FileHandler($file);
        $data = $this->getDataJsonSerializable();

        try {
            $fileHandler->write($data);

            self::assertFileExists($file);
            self::assertSame(
                '{"key":"value","some_key":123,"other_key":[1,6,"2"]}',
                file_get_contents($file)
            );
        } finally {
            unlink($file);
        }
    }

    public function testWritePermissionFailure(): void
    {
        $file = sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true);
        touch($file);

        try {
            chmod($file, 0o444);
            $fileHandler = new FileHandler($file);
            $data = $this->getDataJsonSerializable();

            $this->expectExceptionObject(
                new \RuntimeException(\sprintf('Failed to write to "%s".', $file)),
            );

            $fileHandler->write($data);
        } finally {
            unlink($file);
        }
    }

    public function testWriteDirectoryPermissionFailure(): void
    {
        $directory = sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true);

        try {
            mkdir($directory, 0o555);
            $fileHandler = new FileHandler($directory . '/sub-directory/example.json');
            $data = $this->getDataJsonSerializable();

            $this->expectExceptionObject(
                new \RuntimeException(\sprintf('Failed to create "%s".', $directory . '/sub-directory')),
            );

            $fileHandler->write($data);
        } finally {
            chmod($directory, 0o755);
            rmdir($directory);
        }
    }

    private function getDataJsonSerializable(): \JsonSerializable
    {
        return new class implements \JsonSerializable {
            /**
             * @return array{key: string, some_key: int, other_key: list<int|string>}
             */
            #[\Override]
            public function jsonSerialize(): array
            {
                return ['key' => 'value', 'some_key' => 123, 'other_key' => [1, 6, '2']];
            }
        };
    }
}
