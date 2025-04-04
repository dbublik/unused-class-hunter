<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Parser;

use DBublik\UnusedClassHunter\Cache\CacheInterface;
use DBublik\UnusedClassHunter\Parser\ClassParser;
use DBublik\UnusedClassHunter\Parser\FileParser;
use DBublik\UnusedClassHunter\Tests\Fixtures\Parser\FileParser\ClassNodes;
use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FileParser::class)]
final class FileParserTest extends TestCase
{
    public function testParseException(): void
    {
        $file = __DIR__ . '/../Fixtures/Not/Found';
        $parser = new FileParser(
            parser: ClassParser::create(),
            cache: $this->getNullCache(),
        );

        $this->expectExceptionObject(
            new \RuntimeException(\sprintf('Unable to read file %s', $file)),
        );

        $parser->parse($file);
    }

    public function testParseFile(): void
    {
        $file = __DIR__ . '/../Fixtures/Parser/FileParser/file-nodes.php';
        $parser = new FileParser(
            parser: ClassParser::create(),
            cache: $cache = $this->getNullCache(),
        );

        $fileNode = $parser->parse($file);

        self::assertInstanceOf(FileNode::class, $fileNode);
        self::assertSame($file, $fileNode->getFile());
        self::assertSame([self::class, FileNode::class], $fileNode->getUsedClasses());
        self::assertNotNull($cache->get($file));
    }

    public function testParseFileStrict(): void
    {
        $file = __DIR__ . '/../Fixtures/Parser/FileParser/file-nodes.php';
        $parser = new FileParser(
            parser: ClassParser::create(isStrict: true),
            cache: $this->getNullCache(),
        );

        $fileNode = $parser->parse($file);

        self::assertInstanceOf(FileNode::class, $fileNode);
        self::assertSame([self::class], $fileNode->getUsedClasses());
    }

    public function testParseClass(): void
    {
        $file = __DIR__ . '/../Fixtures/Parser/FileParser/ClassNodes.php';
        $parser = new FileParser(
            parser: ClassParser::create(),
            cache: $this->getNullCache(),
        );

        $fileNode = $parser->parse($file);

        self::assertInstanceOf(ClassNode::class, $fileNode);
        self::assertSame($file, $fileNode->getFile());
        self::assertSame([self::class, 'Attribute', 'stdClass'], $fileNode->getUsedClasses());
        self::assertSame(ClassNodes::class, $fileNode->getName());
        self::assertSame(9, $fileNode->getStartLine());
        self::assertSame(['stdClass'], $fileNode->getExtends());
        self::assertEmpty($fileNode->getImplements());
        self::assertSame(['Attribute'], $fileNode->getAttributes());
    }

    public function testParseClassStrict(): void
    {
        $file = __DIR__ . '/../Fixtures/Parser/FileParser/ClassNodes.php';
        $parser = new FileParser(
            parser: ClassParser::create(isStrict: true),
            cache: $cache = $this->getNullCache(),
        );

        $fileNode = $parser->parse($file);

        self::assertInstanceOf(ClassNode::class, $fileNode);
        self::assertSame(['Attribute', 'stdClass'], $fileNode->getUsedClasses());
        self::assertNotNull($cache->get($file));
    }

    public function testParseFileFromCache(): void
    {
        $parser = new FileParser(
            parser: ClassParser::create(),
            cache: $cache = $this->getNullCache(),
        );
        $cache->set(
            new FileNode(
                file: 'file-path',
                usedClasses: [self::class],
            )
        );

        $fileNode = $parser->parse('file-path');

        self::assertInstanceOf(FileNode::class, $fileNode);
        self::assertSame('file-path', $fileNode->getFile());
        self::assertSame([self::class], $fileNode->getUsedClasses());
    }

    public function testParseClassFromCache(): void
    {
        $parser = new FileParser(
            parser: ClassParser::create(),
            cache: $cache = $this->getNullCache(),
        );
        $cache->set(
            new ClassNode(
                file: 'class-path',
                usedClasses: [\Attribute::class, TestCase::class],
                name: self::class,
                startLine: 11,
                hasApiTag: false,
                extends: [TestCase::class],
                implements: [],
                attributes: [\Attribute::class],
            )
        );

        $classNode = $parser->parse('class-path');

        self::assertInstanceOf(ClassNode::class, $classNode);
        self::assertSame('class-path', $classNode->getFile());
        self::assertSame([\Attribute::class, TestCase::class], $classNode->getUsedClasses());
        self::assertSame(self::class, $classNode->getName());
        self::assertSame(11, $classNode->getStartLine());
        self::assertFalse($classNode->hasApiTag());
        self::assertSame([TestCase::class], $classNode->getExtends());
        self::assertEmpty($classNode->getImplements());
        self::assertSame([\Attribute::class], $classNode->getAttributes());
    }

    private function getNullCache(): CacheInterface
    {
        return new class implements CacheInterface {
            /** @var array<string, AbstractFileNode> */
            private array $files = [];

            #[\Override]
            public function get(string $file): ?AbstractFileNode
            {
                return $this->files[$file] ?? null;
            }

            #[\Override]
            public function set(AbstractFileNode $fileNode): void
            {
                $this->files[$fileNode->getFile()] = $fileNode;
            }
        };
    }
}
