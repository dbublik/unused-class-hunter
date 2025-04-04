<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\ApiTagFilter;
use DBublik\UnusedClassHunter\Parser\ClassNodeTraverser;
use DBublik\UnusedClassHunter\Parser\ClassParser;
use DBublik\UnusedClassHunter\Parser\FileParser;
use DBublik\UnusedClassHunter\Parser\NodeVisitor\ClassNodeVisitor;
use DBublik\UnusedClassHunter\PreFilter\PreFilterInterface;
use DBublik\UnusedClassHunter\Tests\Fixtures\UnusedClassFinder\Example2TestClass;
use DBublik\UnusedClassHunter\Tests\Fixtures\UnusedClassFinder\ExampleMentionedTestClass;
use DBublik\UnusedClassHunter\Tests\Fixtures\UnusedClassFinder\ExampleTestClass;
use DBublik\UnusedClassHunter\UnusedClassFinder;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
#[CoversClass(UnusedClassFinder::class)]
final class UnusedClassFinderTest extends TestCase
{
    #[DataProvider('provideConstructor')]
    public function testConstructor(bool $isStrict): void
    {
        $config = new Config();
        if ($isStrict) {
            $config->allowStrictMode();
        }

        $finder = new UnusedClassFinder($config);

        /** @var FileParser $fileParser */
        $fileParser = (new \ReflectionProperty(UnusedClassFinder::class, 'fileParser'))->getValue($finder);

        /** @var ClassParser $classParser */
        $classParser = (new \ReflectionProperty(FileParser::class, 'parser'))->getValue($fileParser);

        /** @var ClassNodeTraverser $traverser */
        $traverser = (new \ReflectionProperty(ClassParser::class, 'traverser'))->getValue($classParser);

        /** @var ClassNodeVisitor $visitor */
        $visitor = (new \ReflectionProperty(ClassNodeTraverser::class, 'visitor'))->getValue($traverser);

        self::assertSame(
            $isStrict,
            (new \ReflectionProperty(ClassNodeVisitor::class, 'isStrict'))->getValue($visitor)
        );
    }

    /**
     * @return iterable<array{0: bool}>
     */
    public static function provideConstructor(): iterable
    {
        yield [false];

        yield [true];
    }

    public function testFindClasses(): void
    {
        $config = (new Config())
            ->setFinder(
                Finder::create()
                    ->in(__DIR__ . '/Fixtures/UnusedClassFinder')
                    ->append([__DIR__ . '/Fixtures/Not/Found/file.php'])
            )
            ->setCacheDir(sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true))
            ->withFilters(new ApiTagFilter());
        $finder = new UnusedClassFinder($config);
        $io = new SymfonyStyle(new ArrayInput([]), $output = new BufferedOutput());

        $unusedClasses = $finder->findClasses($io);

        self::assertSame(
            [Example2TestClass::class, ExampleTestClass::class],
            array_map(
                static fn (ClassNode $node): string => $node->getName(),
                $unusedClasses
            )
        );
        self::assertSame(
            '
 1/4 [▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░]  25%
 4/4 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

',
            $output->fetch()
        );
    }

    public function testFindClassesPreFilter(): void
    {
        $config = (new Config())
            ->setFinder(
                Finder::create()
                    ->in(__DIR__ . '/Fixtures/UnusedClassFinder')
                    ->append([__DIR__ . '/Fixtures/Not/Found/file.php'])
            )
            ->setCacheDir(sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true))
            ->withPreFilters(
                new class implements PreFilterInterface {
                    #[\Override]
                    public function isUnused(ClassNode $class, ReaderResult $reader): bool
                    {
                        return ExampleMentionedTestClass::class === $class->getName();
                    }
                }
            )
            ->withFilters(new ApiTagFilter());
        $finder = new UnusedClassFinder($config);
        $io = new SymfonyStyle(new ArrayInput([]), $output = new BufferedOutput());

        $unusedClasses = $finder->findClasses($io);

        self::assertSame(
            [Example2TestClass::class, ExampleMentionedTestClass::class, ExampleTestClass::class],
            array_map(
                static fn (ClassNode $node): string => $node->getName(),
                $unusedClasses
            )
        );
        self::assertSame(
            '
 1/4 [▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░]  25%
 4/4 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

',
            $output->fetch()
        );
    }

    public function testDeleteClasses(): void
    {
        $finder = new UnusedClassFinder(new Config());
        $file1 = new ClassNode(
            file: sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true),
            usedClasses: [],
            name: self::class,
            startLine: 1,
        );
        file_put_contents($file1->getFile(), 'test');
        $file2 = new ClassNode(
            file: sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true),
            usedClasses: [],
            name: self::class,
            startLine: 1,
        );
        file_put_contents($file2->getFile(), 'test2');

        $finder->deleteClasses($file1, $file2);

        self::assertFileDoesNotExist($file1->getFile());
        self::assertFileDoesNotExist($file2->getFile());
    }
}
