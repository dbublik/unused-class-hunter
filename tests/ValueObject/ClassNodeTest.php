<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\ValueObject;

use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ClassNode::class)]
#[CoversClass(AbstractFileNode::class)]
final class ClassNodeTest extends TestCase
{
    public function testConstructor(): void
    {
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [TestCase::class],
            name: self::class,
            startLine: 10,
            hasApiTag: true,
            extends: [TestCase::class],
            implements: [TestCase::class],
            attributes: [CoversClass::class],
        );

        self::assertSame('file.txt', $classNode->getFile());
        self::assertSame([TestCase::class], $classNode->getUsedClasses());
        self::assertSame(self::class, $classNode->getName());
        self::assertSame(10, $classNode->getStartLine());
        self::assertTrue($classNode->hasApiTag());
        self::assertSame([TestCase::class], $classNode->getExtends());
        self::assertSame([TestCase::class], $classNode->getImplements());
        self::assertSame([CoversClass::class], $classNode->getAttributes());
    }

    public function testConstructorSimple(): void
    {
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
        );

        self::assertSame('file.txt', $classNode->getFile());
        self::assertEmpty($classNode->getUsedClasses());
        self::assertSame(self::class, $classNode->getName());
        self::assertSame(1, $classNode->getStartLine());
        self::assertFalse($classNode->hasApiTag());
        self::assertEmpty($classNode->getExtends());
        self::assertEmpty($classNode->getImplements());
        self::assertEmpty($classNode->getAttributes());
    }

    public function testConstructorFileException(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('Value must be a non empty string')
        );

        // @phpstan-ignore argument.type
        new ClassNode(file: '', usedClasses: [], name: self::class, startLine: 1);
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideConstructorListException')]
    public function testConstructorUsedClassesException(string $exceptionMessage, array $usedClasses): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        new ClassNode(
            file: 'file.txt',
            // @phpstan-ignore argument.type
            usedClasses: $usedClasses,
            name: self::class,
            startLine: 1,
        );
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideConstructorListException')]
    public function testConstructorExtendsException(string $exceptionMessage, array $extends): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            // @phpstan-ignore argument.type
            extends: $extends,
        );
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideConstructorListException')]
    public function testConstructorImplementsException(string $exceptionMessage, array $implements): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            // @phpstan-ignore argument.type
            implements: $implements,
        );
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideConstructorListException')]
    public function testConstructorAttributesException(string $exceptionMessage, array $attributes): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
            // @phpstan-ignore argument.type
            attributes: $attributes,
        );
    }

    /**
     * @return iterable<array{0: string, 1: array}>
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function provideConstructorListException(): iterable
    {
        yield [
            'Array must have a list of strings',
            ['key' => 'value'],
        ];

        yield [
            'Value must be a string, got "integer"',
            [1],
        ];
    }

    public function testJsonSerializeSimple(): void
    {
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [],
            name: self::class,
            startLine: 1,
        );

        $json = json_encode($classNode, JSON_THROW_ON_ERROR);

        self::assertSame(
            \sprintf(
                '{"file":"file.txt","usedClasses":[],"class":'
                . '{"name":"%s","startLine":1,"hasApiTag":false,"extends":[],"implements":[],"attributes":[]}'
                . '}',
                addcslashes(self::class, '\\'),
            ),
            $json
        );
    }

    public function testJsonSerialize(): void
    {
        $classNode = new ClassNode(
            file: 'file.txt',
            usedClasses: [TestCase::class],
            name: self::class,
            startLine: 10,
            hasApiTag: true,
            extends: [TestCase::class],
            implements: [TestCase::class],
            attributes: [CoversClass::class],
        );

        $json = json_encode($classNode, JSON_THROW_ON_ERROR);

        self::assertSame(
            \sprintf(
                '{"file":"file.txt","usedClasses":["%s"],"class":'
                . '{"name":"%s","startLine":10,"hasApiTag":true,"extends":["%s"],"implements":["%s"],"attributes":["%s"]}'
                . '}',
                addcslashes(TestCase::class, '\\'),
                addcslashes(self::class, '\\'),
                addcslashes(TestCase::class, '\\'),
                addcslashes(TestCase::class, '\\'),
                addcslashes(CoversClass::class, '\\'),
            ),
            $json
        );
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    #[DataProvider('provideFromData')]
    public function testFromData(bool $isValid, array $data): void
    {
        $classNode = ClassNode::fromData($data);

        self::assertSame($isValid, null !== $classNode);
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
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 123,
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => -1,
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => '',
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => '',
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => [1],
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => [],
                    'implements' => '',
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => [],
                    'implements' => [new \stdClass()],
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => [],
                    'implements' => [],
                    'attributes' => '',
                ],
            ],
        ];

        yield [
            false,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => [],
                    'implements' => [],
                    'attributes' => [[]],
                ],
            ],
        ];

        yield [
            true,
            [
                'file' => 'file.php',
                'usedClasses' => [],
                'class' => [
                    'name' => 'name',
                    'startLine' => 1,
                    'hasApiTag' => false,
                    'extends' => [],
                    'implements' => [],
                    'attributes' => [],
                ],
            ],
        ];

        yield [
            true,
            [
                'file' => 'file.php',
                'usedClasses' => [self::class],
                'class' => [
                    'name' => 'name',
                    'startLine' => 10,
                    'hasApiTag' => true,
                    'extends' => [self::class],
                    'implements' => [self::class],
                    'attributes' => [self::class],
                ],
            ],
        ];
    }
}
