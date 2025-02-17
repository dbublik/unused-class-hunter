<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Parser;

use DBublik\UnusedClassHunter\Parser\ClassParser;
use PhpParser\Lexer;
use PhpParser\Parser\Php8;
use PhpParser\PhpVersion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ClassParser::class)]
final class ClassParserTest extends TestCase
{
    public function testCreate(): void
    {
        $classParser = ClassParser::create();

        $parser = (new \ReflectionProperty($classParser, 'parser'))->getValue($classParser);
        self::assertInstanceOf(Php8::class, $parser);
        $lexer = (new \ReflectionProperty($parser, 'lexer'))->getValue($parser);
        self::assertInstanceOf(Lexer::class, $lexer);
        $phpVersion = (new \ReflectionProperty($parser, 'phpVersion'))->getValue($parser);
        self::assertInstanceOf(PhpVersion::class, $phpVersion);
        self::assertSame(80300, $phpVersion->id);
    }

    public function testParseSyntaxException(): void
    {
        $classParser = ClassParser::create();
        $code = '<?php use';

        $this->expectExceptionObject(
            new \RuntimeException('Syntax error, unexpected EOF on line 1')
        );

        $classParser->parse($code);
    }

    public function testParseWithoutClass(): void
    {
        $classParser = ClassParser::create();
        $code = <<<'PHP'
            <?php
            use Example\Directory\FifthClass;
            use Example\UnusedClass;

            const EXAMPLE_CONST = FirstClass::class;
            const EXAMPLE_OTHER_CONST = Example\Directory\SubDirectory\SecondClass::class;

            (new \Example\ThirdClass())->doSmth();

            function example(\Example\FourthClass $param, FifthClass $otherParam): void
            {
                Example\Other\SixthClass::example();
            }
            PHP;

        $parsedFile = $classParser->parse($code);

        self::assertSame(
            [
                'Example\Directory\FifthClass',
                'Example\UnusedClass',
                'FirstClass',
                'Example\Directory\SubDirectory\SecondClass',
                'Example\ThirdClass',
                'Example\FourthClass',
                'Example\Other\SixthClass',
            ],
            $parsedFile->getUsedClasses(),
        );
        self::assertNull($parsedFile->getClassName());
    }

    public function testParseClass(): void
    {
        $classParser = ClassParser::create();
        $code = <<<'PHP'
            <?php
            namespace My\Something;

            use Example\FirstClass;
            use Example\PhpdocUsedOnlyClass;

            /** @api */
            #[Attribute\FirstAttribute]
            class MyClass extends \AbstractClass implements \Example\FirstInterface, SecondInterface
            {
                const const = FirstClass::class;

                private ?\Example\SecondClass $secondClass = null;
                public null|\Example\Directory\ThirdClass|FourthClass $thirdClass = null;

                public function __construct(
                    protected \Example\Other\FifthClass $fifthClass,
                ) {}

                /**
                 * @return PhpdocUsedOnlyClass
                 */
                #[\SecondAttribute(SomethingClass::class)]
                public function someMethod(): object
                {
                    return Example\Directory\Other\SixthClass::doRun(
                        new \SeventhClass(),
                        \Example\Example\Eighth::SOME_CONST,
                    );
                }
            }
            PHP;

        $parsedFile = $classParser->parse($code);

        self::assertSame(
            [
                'Example\FirstClass',
                'Example\PhpdocUsedOnlyClass',
                'My\Something\Attribute\FirstAttribute',
                'AbstractClass',
                'Example\FirstInterface',
                'My\Something\SecondInterface',
                'Example\SecondClass',
                'Example\Directory\ThirdClass',
                'My\Something\FourthClass',
                'Example\Other\FifthClass',
                'SecondAttribute',
                'My\Something\SomethingClass',
                'My\Something\Example\Directory\Other\SixthClass',
                'SeventhClass',
                'Example\Example\Eighth',
            ],
            $parsedFile->getUsedClasses()
        );
        self::assertSame('My\Something\MyClass', $parsedFile->getClassName());
        self::assertSame(8, $parsedFile->getClassStartLine());
        self::assertTrue($parsedFile->hasClassApiTag());
        self::assertSame(['AbstractClass'], $parsedFile->getExtends());
        self::assertSame(['Example\FirstInterface', 'My\Something\SecondInterface'], $parsedFile->getImplements());
        self::assertSame(['My\Something\Attribute\FirstAttribute', 'SecondAttribute'], $parsedFile->getAttributes());
    }

    public function testParseAliases(): void
    {
        $classParser = ClassParser::create();
        $code = <<<'PHP'
            <?php
            namespace Example;

            use Example\Directory as SubDirectory;
            use Example\Directory\SecondClass;

            class MyClass
            {
                const const = SubDirectory\FirstClass::class;

                public ?SecondClass $secondClass = null;
            }
            PHP;

        $parsedFile = $classParser->parse($code);

        self::assertSame(
            [
                'Example\Directory',
                'Example\Directory\SecondClass',
                'Example\Directory\FirstClass',
            ],
            $parsedFile->getUsedClasses()
        );
        self::assertSame('Example\MyClass', $parsedFile->getClassName());
        self::assertSame(7, $parsedFile->getClassStartLine());
        self::assertFalse($parsedFile->hasClassApiTag());
        self::assertEmpty($parsedFile->getExtends());
        self::assertEmpty($parsedFile->getImplements());
        self::assertEmpty($parsedFile->getAttributes());
    }

    public function testParseStrict(): void
    {
        $classParser = ClassParser::create(isStrict: true);
        $code = <<<'PHP'
            <?php
            use Example\Directory as SubDirectory;
            use Example\UnusedClass;
            use Example\PhpdocUsedOnlyClass;

            /**
             * @return class-string<FirstClass|PhpdocUsedOnlyClass>
             */
            function someMethod(): string
            {
                return SubDirectory\FirstClass::class;
            }
            PHP;

        $parsedFile = $classParser->parse($code);

        self::assertSame(['Example\Directory\FirstClass'], $parsedFile->getUsedClasses());
    }
}
