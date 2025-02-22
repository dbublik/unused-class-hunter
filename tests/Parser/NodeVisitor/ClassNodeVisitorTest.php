<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Parser\NodeVisitor;

use DBublik\UnusedClassHunter\Parser\NodeVisitor\ClassNodeVisitor;
use DBublik\UnusedClassHunter\Parser\ParsedFile;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ClassNodeVisitor::class)]
final class ClassNodeVisitorTest extends TestCase
{
    public function testBeforeTraverse(): void
    {
        $parsedFile = new ParsedFile();
        $parsedFile->setClassName(self::class);
        $visitor = new ClassNodeVisitor(parsedFile: $parsedFile);
        $node1 = new Node\Name(self::class);
        $node2 = new Node\Name(TestCase::class);

        $nodes = $visitor->beforeTraverse([$node1, $node2]);

        self::assertSame([$node1, $node2], $nodes);
        self::assertNotSame($parsedFile, $newParsedFile = $visitor->getParsedFile());
        self::assertNull($newParsedFile->getClassName());
    }

    public function testEnterNodeConstFetch(): void
    {
        $visitor = new ClassNodeVisitor();
        $node = (new BuilderFactory())->constFetch('null');

        $result = $visitor->enterNode($node);

        self::assertSame(NodeVisitor::DONT_TRAVERSE_CHILDREN, $result);
        self::assertEmpty($visitor->getParsedFile()->getUsedClasses());
    }

    #[DataProvider('provideClassLike')]
    public function testEnterNodeClassLike(Node\Stmt\ClassLike $node, ParsedFile $expectedParsedFile): void
    {
        $visitor = new ClassNodeVisitor();

        $result = $visitor->enterNode($node);

        self::assertNull($result);
        $parsedFile = $visitor->getParsedFile();
        self::assertEmpty($parsedFile->getUsedClasses());
        self::assertSame($expectedParsedFile->getClassName(), $parsedFile->getClassName());
        self::assertSame($expectedParsedFile->getClassStartLine(), $parsedFile->getClassStartLine());
        self::assertSame($expectedParsedFile->hasClassApiTag(), $parsedFile->hasClassApiTag());
        self::assertSame($expectedParsedFile->getExtends(), $parsedFile->getExtends());
        self::assertSame($expectedParsedFile->getImplements(), $parsedFile->getImplements());
        self::assertSame($expectedParsedFile->getAttributes(), $parsedFile->getAttributes());
    }

    /**
     * @return iterable<array{0: Node\Stmt\ClassLike, 1: ParsedFile}>
     */
    public static function provideClassLike(): iterable
    {
        $builder = new BuilderFactory();

        yield 'not traversed if no namespacedName is found' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');

                $node = $class->getNode();
                $node->namespacedName = null;

                return $node;
            })(),
            new ParsedFile(),
        ];

        yield 'there are api tag and start line - 1' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');
                $class->setDocComment('/** @api */');

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);
                $node->setAttribute('startLine', 5);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->setClassStartLine(5);
                $file->setHasClassApiTag(true);

                return $file;
            })(),
        ];

        yield 'there are api tag and start line - 2' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');
                $class->setDocComment('/**@api*/');

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);
                $node->setAttribute('startLine', 0);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->setClassStartLine(0);
                $file->setHasClassApiTag(true);

                return $file;
            })(),
        ];

        yield 'there are api tag and start line - 3' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');
                $class->setDocComment("/**\n@api\n*/");

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);
                $node->setAttribute('startLine', 1);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->setClassStartLine(1);
                $file->setHasClassApiTag(true);

                return $file;
            })(),
        ];

        yield 'there are not api tag and start line - 1' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');
                $class->setDocComment('/** @apii */');

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);
                $node->setAttribute('startLine', null);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);

                return $file;
            })(),
        ];

        yield 'there are not api tag and start line - 2' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');
                $class->setDocComment('/** @aPi */');

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);
                $node->setAttribute('startLine', -1);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);

                return $file;
            })(),
        ];

        yield 'get empty class' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);

                return $file;
            })(),
        ];

        yield 'get class' => [
            (static function () use ($builder): Node\Stmt\ClassLike {
                $class = $builder->class('ClassName');
                $class->extend(TestCase::class);
                $class->implement(Reorderable::class, Test::class);
                $class->addAttribute($builder->attribute(DataProvider::class));

                $node = $class->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->addExtends(TestCase::class);
                $file->addImplement(Reorderable::class);
                $file->addImplement(Test::class);
                $file->addAttribute(DataProvider::class);

                return $file;
            })(),
        ];

        yield 'get empty interface' => [
            (static function () use ($builder): Node\Stmt\Interface_ {
                $interface = $builder->interface('InterfaceName');

                $node = $interface->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);

                return $file;
            })(),
        ];

        yield 'get interface' => [
            (static function () use ($builder): Node\Stmt\Interface_ {
                $interface = $builder->interface('InterfaceName');
                $interface->extend(Reorderable::class, Test::class);
                $interface->addAttribute($builder->attribute(DataProvider::class));

                $node = $interface->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->addExtends(Reorderable::class);
                $file->addExtends(Test::class);
                $file->addAttribute(DataProvider::class);

                return $file;
            })(),
        ];

        yield 'get empty trait' => [
            (static function () use ($builder): Node\Stmt\Trait_ {
                $trait = $builder->trait('TraitName');

                $node = $trait->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);

                return $file;
            })(),
        ];

        yield 'get trait' => [
            (static function () use ($builder): Node\Stmt\Trait_ {
                $trait = $builder->trait('TraitName');
                $trait->addAttribute($builder->attribute(DataProvider::class));

                $node = $trait->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->addAttribute(DataProvider::class);

                return $file;
            })(),
        ];

        yield 'get empty enum' => [
            (static function () use ($builder): Node\Stmt\Enum_ {
                $enum = $builder->enum('EnumName');

                $node = $enum->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);

                return $file;
            })(),
        ];

        yield 'get enum' => [
            (static function () use ($builder): Node\Stmt\Enum_ {
                $enum = $builder->enum('EnumName');
                $enum->addAttribute($builder->attribute(DataProvider::class));

                $node = $enum->getNode();
                $node->namespacedName = new Node\Name(self::class);

                return $node;
            })(),
            (static function (): ParsedFile {
                $file = new ParsedFile();
                $file->setClassName(self::class);
                $file->addAttribute(DataProvider::class);

                return $file;
            })(),
        ];
    }

    /**
     * @param list<class-string> $attributes
     */
    #[DataProvider('provideClassMethod')]
    public function testEnterNodeClassMethod(Node\Stmt\ClassMethod $node, array $attributes): void
    {
        $visitor = new ClassNodeVisitor();

        $result = $visitor->enterNode($node);

        self::assertNull($result);
        $parsedFile = $visitor->getParsedFile();
        self::assertEmpty($parsedFile->getUsedClasses());
        self::assertNull($parsedFile->getClassName());
        self::assertSame(0, $parsedFile->getClassStartLine());
        self::assertFalse($parsedFile->hasClassApiTag());
        self::assertEmpty($parsedFile->getExtends());
        self::assertEmpty($parsedFile->getImplements());
        self::assertSame($attributes, $parsedFile->getAttributes());
    }

    /**
     * @return iterable<array{0: Node\Stmt\ClassMethod, 1: list<class-string>}>
     */
    public static function provideClassMethod(): iterable
    {
        $builder = new BuilderFactory();

        yield 'empty method' => [
            $builder->method('classMethod')->getNode(),
            [],
        ];

        yield 'there are some attributes' => [
            (static function () use ($builder): Node\Stmt\ClassMethod {
                $method = $builder->method('classMethod');
                $method->addAttribute($builder->attribute(DataProvider::class));
                $method->addAttribute($builder->attribute(DataProviderExternal::class));

                return $method->getNode();
            })(),
            [DataProvider::class, DataProviderExternal::class],
        ];
    }

    #[DataProvider('provideNodeNameNotTraversed')]
    public function testEnterNodeNameNotTraversed(Node\Name $node, bool $isStrict = false): void
    {
        $visitor = new ClassNodeVisitor(isStrict: $isStrict);

        $result = $visitor->enterNode($node);

        self::assertNull($result);
        self::assertEmpty($visitor->getParsedFile()->getUsedClasses());
    }

    /**
     * @return iterable<array{0: Node\Name}>
     */
    public static function provideNodeNameNotTraversed(): iterable
    {
        $builder = new BuilderFactory();

        yield 'self special class name' => [new Node\Name('self')];

        yield 'parent special class name' => [new Node\Name('parent')];

        yield 'static special class name' => [new Node\Name('static')];

        yield 'namespace parent node' => [
            new Node\Name(self::class, ['parent' => $builder->namespace('NamespaceName')->getNode()]),
        ];

        yield 'function call parent node' => [
            new Node\Name(self::class, ['parent' => $builder->funcCall('FuncCallName')]),
        ];

        yield 'class method parent node' => [
            new Node\Name(self::class, ['parent' => $builder->method('MethodName')->getNode()]),
        ];

        yield 'use item parent node in strict mode' => [
            new Node\Name(self::class, [
                'parent' => $builder->use('UseName')->getNode()->uses[0] ?? throw new \RuntimeException(),
            ]),
            true,
        ];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('provideNodeName')]
    public function testEnterNodeName(Node\Name $node, string $className): void
    {
        $visitor = new ClassNodeVisitor();

        $result = $visitor->enterNode($node);

        self::assertNull($result);
        self::assertSame([$className], $visitor->getParsedFile()->getUsedClasses());
    }

    /**
     * @return iterable<array{0: Node\Name, 1: class-string}>
     */
    public static function provideNodeName(): iterable
    {
        $builder = new BuilderFactory();

        yield [
            new Node\Name(self::class),
            self::class,
        ];

        yield [
            new Node\Name(self::class, [
                'parent' => $builder->use('UseName')->getNode()->uses[0] ?? throw new \RuntimeException(),
            ]),
            self::class,
        ];
    }
}
