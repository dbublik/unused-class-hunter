<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Cache;

use DBublik\UnusedClassHunter\Cache\Cache;
use DBublik\UnusedClassHunter\Cache\FileHandler;
use DBublik\UnusedClassHunter\Cache\Signature;
use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Package;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Cache::class)]
final class CacheTest extends TestCase
{
    /** @var non-empty-string */
    private string $cacheDir;

    /** @var non-empty-string */
    private string $classesDir;

    /** @var non-empty-string */
    private string $rootCacheFile;

    #[\Override]
    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true);
        $this->classesDir = $this->cacheDir . '/classes';
        $this->rootCacheFile = $this->cacheDir . '/.hunter.cache';
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->deleteFolder($this->cacheDir);
    }

    public function testCreate(): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        mkdir($this->cacheDir, recursive: true);

        $cache = Cache::create($config);

        /** @var FileHandler $rootCacheFile */
        $rootCacheFile = (new \ReflectionProperty(Cache::class, 'rootCacheFile'))->getValue($cache);
        self::assertSame($this->rootCacheFile, $rootCacheFile->getName());
        self::assertFileExists($this->rootCacheFile);

        /** @var Signature $signature */
        $signature = (new \ReflectionProperty(Cache::class, 'signature'))->getValue($cache);
        self::assertSame(
            \sprintf(
                '{"phpVersion":"%s","packageVersion":"%s","config":{"isStrictMode":false,"bootstrapFiles":[]}}',
                PHP_VERSION,
                Package::getVersion(),
            ),
            $signatureJson = json_encode($signature, JSON_THROW_ON_ERROR),
        );
        self::assertSame(
            $signatureJson,
            (string) file_get_contents($this->rootCacheFile)
        );

        /** @var bool $signatureWasUpdated */
        $signatureWasUpdated = (new \ReflectionProperty(Cache::class, 'signatureWasUpdated'))->getValue($cache);
        self::assertTrue($signatureWasUpdated);

        /** @var list<string> $oldFiles */
        $oldFiles = (new \ReflectionProperty(Cache::class, 'oldFiles'))->getValue($cache);
        self::assertEmpty($oldFiles);
    }

    #[DataProvider('provideCreateRootCacheFile')]
    public function testCreateRootCacheFile(string $rootCacheContent, bool $expectedSignatureWasUpdated): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        mkdir($this->cacheDir, recursive: true);
        file_put_contents($this->rootCacheFile, $rootCacheContent);

        $cache = Cache::create($config);

        /** @var bool $signatureWasUpdated */
        $signatureWasUpdated = (new \ReflectionProperty(Cache::class, 'signatureWasUpdated'))->getValue($cache);
        self::assertSame($expectedSignatureWasUpdated, $signatureWasUpdated);
    }

    /**
     * @return iterable<array{0: null|string, 1: bool}>
     */
    public static function provideCreateRootCacheFile(): iterable
    {
        yield 'wrong file' => [
            '',
            true,
        ];

        yield 'wrong file content' => [
            '{"phpVersion":"1',
            true,
        ];

        yield 'not equal file content' => [
            \sprintf(
                '{"phpVersion":"%s","packageVersion":"%s","config":{"isStrictMode":true,"bootstrapFiles":[]}}',
                PHP_VERSION,
                Package::getVersion(),
            ),
            true,
        ];

        yield 'equal file content' => [
            \sprintf(
                '{"phpVersion":"%s","packageVersion":"%s","config":{"isStrictMode":false,"bootstrapFiles":[]}}',
                PHP_VERSION,
                Package::getVersion(),
            ),
            false,
        ];
    }

    public function testDestructor(): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        mkdir($this->classesDir, recursive: true);
        file_put_contents($file1 = $this->classesDir . '/file1.txt', 'test');
        file_put_contents($file2 = $this->classesDir . '/file2.txt', 'test');
        file_put_contents($file3 = $this->classesDir . '/file3.txt', 'test');
        $cache = Cache::create($config);
        (new \ReflectionProperty(Cache::class, 'newFiles'))->setValue($cache, [$file2]);

        unset($cache);

        self::assertFileDoesNotExist($file1);
        self::assertFileExists($file2);
        self::assertFileDoesNotExist($file3);
    }

    public function testGetSignatureWasUpdated(): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        $cache = Cache::create($config);

        $node = $cache->get('file.php');

        self::assertNull($node);
    }

    public function testGetFileNotFound(): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        $cache = Cache::create($config);
        (new \ReflectionProperty(Cache::class, 'signatureWasUpdated'))->setValue($cache, false);

        $node = $cache->get(__FILE__);

        self::assertNull($node);
    }

    #[DataProvider('provideGetNodeWrong')]
    public function testGetNodeWrong(string $content): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        $cache = Cache::create($config);
        (new \ReflectionProperty(Cache::class, 'signatureWasUpdated'))->setValue($cache, false);
        $this->createClassFile($content);

        $node = $cache->get(__FILE__);

        self::assertNull($node);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public static function provideGetNodeWrong(): iterable
    {
        yield 'file node' => [
            '{"file":"","usedClasses":[]}',
        ];

        yield 'class node' => [
            '{"file":"file.txt","usedClasses":[],"class":[]}',
        ];
    }

    /**
     * @param class-string $nodeClass
     */
    #[DataProvider('provideGetNode')]
    public function testGetNode(string $nodeClass, string $content): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        $cache = Cache::create($config);
        (new \ReflectionProperty(Cache::class, 'signatureWasUpdated'))->setValue($cache, false);
        $file = $this->createClassFile($content);

        $node = $cache->get(__FILE__);

        self::assertNotNull($node);
        self::assertInstanceOf($nodeClass, $node);
        self::assertSame([$file], (new \ReflectionProperty(Cache::class, 'newFiles'))->getValue($cache));
    }

    /**
     * @return iterable<array{0: class-string, 1: string}>
     */
    public static function provideGetNode(): iterable
    {
        yield 'file node' => [
            FileNode::class,
            '{"file":"file.txt","usedClasses":[]}',
        ];

        yield 'class node' => [
            ClassNode::class,
            '{"file":"file.txt","usedClasses":[],"class":'
            . '{"name":"name","startLine":1,"hasApiTag":false,"extends":[],"implements":[],"attributes":[]}'
            . '}',
        ];
    }

    public function testSet(): void
    {
        $config = (new Config())->setCacheDir($this->cacheDir);
        $cache = Cache::create($config);
        $file = $this->getClassFilename();
        $node = new FileNode(self::class, []);

        $cache->set(__FILE__, $node);

        self::assertFileExists($file);
        self::assertSame(
            [$file],
            (new \ReflectionProperty(Cache::class, 'newFiles'))->getValue($cache)
        );
    }

    private function deleteFolder(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        /** @var list<string> $files */
        $files = scandir($directory);

        foreach (array_diff($files, ['.', '..']) as $file) {
            $filePath = $directory . \DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                $this->deleteFolder($filePath);
            } else {
                unlink($filePath);
            }
        }

        rmdir($directory);
    }

    private function getClassFilename(): string
    {
        return $this->classesDir . '/' . hash_file('sha256', __FILE__);
    }

    private function createClassFile(string $content): string
    {
        $file = $this->getClassFilename();

        mkdir($this->classesDir, recursive: true);
        file_put_contents($file, $content);

        return $file;
    }
}
