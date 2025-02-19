<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Parser;

use DBublik\UnusedClassHunter\Parser\ParsedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ParsedFile::class)]
final class ParsedFileTest extends TestCase
{
    public function testConstructor(): void
    {
        $parsedFile = new ParsedFile();

        self::assertEmpty($parsedFile->getUsedClasses());
        self::assertNull($parsedFile->getClassName());
        self::assertSame(0, $parsedFile->getClassStartLine());
        self::assertFalse($parsedFile->hasClassApiTag());
        self::assertEmpty($parsedFile->getExtends());
        self::assertEmpty($parsedFile->getImplements());
        self::assertEmpty($parsedFile->getAttributes());
    }

    public function testAddUsedClass(): void
    {
        $parsedFile = new ParsedFile();
        $usedClasses = [self::class, self::class, ParsedFile::class];

        foreach ($usedClasses as $usedClass) {
            $parsedFile->addUsedClass($usedClass);
        }

        self::assertSame([self::class, ParsedFile::class], $parsedFile->getUsedClasses());
    }

    public function testSetClassName(): void
    {
        $parsedFile = new ParsedFile();
        $className = self::class;

        $parsedFile->setClassName($className);

        self::assertSame(self::class, $parsedFile->getClassName());
    }

    public function testSetClassStartLine(): void
    {
        $parsedFile = new ParsedFile();
        $startLine = 11;

        $parsedFile->setClassStartLine($startLine);

        self::assertSame($startLine, $parsedFile->getClassStartLine());
    }

    public function testSetHasClassApiTag(): void
    {
        $parsedFile = new ParsedFile();

        $parsedFile->setHasClassApiTag(true);

        self::assertTrue($parsedFile->hasClassApiTag());
    }

    public function testAddExtends(): void
    {
        $parsedFile = new ParsedFile();
        $extends = [self::class, self::class, ParsedFile::class];

        foreach ($extends as $extend) {
            $parsedFile->addExtends($extend);
        }

        self::assertSame($extends, $parsedFile->getExtends());
    }

    public function testAddImplement(): void
    {
        $parsedFile = new ParsedFile();
        $implements = [self::class, self::class, ParsedFile::class];

        foreach ($implements as $implement) {
            $parsedFile->addImplement($implement);
        }

        self::assertSame($implements, $parsedFile->getImplements());
    }

    public function testAddAttribute(): void
    {
        $parsedFile = new ParsedFile();
        $attributes = [self::class, self::class, ParsedFile::class];

        foreach ($attributes as $attribute) {
            $parsedFile->addAttribute($attribute);
        }

        self::assertSame($attributes, $parsedFile->getAttributes());
    }
}
