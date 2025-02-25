<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\Filter\AutoconfigureTagAttributeFilter;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\Filter\CodeceptionTestClassFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\PreFilter\PreFilterInterface;
use DBublik\UnusedClassHunter\Sets\SetInterface;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\FileTypeFilterIterator;
use Symfony\Component\Finder\Iterator\SortableIterator;

/**
 * @internal
 */
#[CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new Config();

        $finder = $config->getFinder();
        self::assertFinderPropertySame($finder, 'dirs', [getcwd()]);
        self::assertFinderPropertySame($finder, 'exclude', ['var', 'vendor']);
        self::assertFinderPropertySame($finder, 'sort', SortableIterator::SORT_BY_NAME);
        self::assertFinderPropertySame($finder, 'mode', FileTypeFilterIterator::ONLY_FILES);
        self::assertFinderPropertySame($finder, 'iterators', []);
        self::assertSame(sys_get_temp_dir() . '/unused-class-hunter', $config->getCacheDir());
        self::assertFalse($config->isStrictMode());
        self::assertEmpty($config->getBootstrapFiles());
        self::assertEmpty($config->getPreFilters());
        self::assertSame(
            [
                ClassFilter::class,
                AttributeFilter::class,
            ],
            array_map(
                static fn (FilterInterface $filter): string => $filter::class,
                $config->getFilters(),
            )
        );
        self::assertEmpty($config->getIgnoredClasses());
        self::assertEmpty($config->getIgnoredAttributes());
    }

    public function testSetFinder(): void
    {
        $config = new Config();
        $finder = Finder::create()
            ->in(__DIR__)
            ->append([__FILE__])
            ->exclude('tests');

        $config->setFinder($finder);

        $finder = $config->getFinder();
        self::assertFinderPropertySame($finder, 'dirs', [__DIR__]);
        self::assertFinderPropertySame($finder, 'exclude', ['tests', 'var', 'vendor']);
        self::assertFinderPropertySame($finder, 'sort', SortableIterator::SORT_BY_NAME);
        self::assertFinderPropertySame($finder, 'mode', FileTypeFilterIterator::ONLY_FILES);
    }

    public function testSetCacheDir(): void
    {
        $config = new Config();
        $cacheDir = __DIR__ . '/../var/cache/phpunit';

        $config->setCacheDir($cacheDir);

        self::assertSame($cacheDir, $config->getCacheDir());
    }

    public function testAllowStrictMode(): void
    {
        $config = new Config();

        $config->allowStrictMode();

        self::assertTrue($config->isStrictMode());
    }

    public function testWithBootstrapFiles(): void
    {
        $config = new Config();
        $file = __DIR__ . '/../vendor/autoload.php';

        $config->withBootstrapFiles($file);

        self::assertSame([$file], $config->getBootstrapFiles());
    }

    public function testWithBootstrapFilesNotExists(): void
    {
        $config = new Config();
        $file = __DIR__ . '/kbczkjxhoahb';

        $this->expectExceptionObject(
            new \InvalidArgumentException(\sprintf('Bootstrap file "%s" does not exist', $file))
        );

        $config->withBootstrapFiles($file);
    }

    public function testWithBootstrapFilesNotFile(): void
    {
        $config = new Config();
        $file = __DIR__ . '/../vendor';

        $this->expectExceptionObject(
            new \InvalidArgumentException(\sprintf('Bootstrap file "%s" does not exist', $file))
        );

        $config->withBootstrapFiles($file);
    }

    public function testWithPreFilters(): void
    {
        $config = new Config();
        $customPreFilter = new class implements PreFilterInterface {
            #[\Override]
            public function isUnused(ClassNode $class, ReaderResult $reader): bool
            {
                return false;
            }
        };

        $config->withPreFilters($customPreFilter);

        self::assertSame(
            [$customPreFilter::class],
            array_map(
                static fn (PreFilterInterface $preFilter): string => $preFilter::class,
                $config->getPreFilters(),
            )
        );
    }

    public function testWithFilters(): void
    {
        $config = new Config();
        $customFilter = new class implements FilterInterface {
            #[\Override]
            public function isIgnored(ClassNode $class, ReaderResult $reader): bool
            {
                return false;
            }
        };

        $config->withFilters($customFilter);

        self::assertSame(
            [
                ClassFilter::class,
                AttributeFilter::class,
                $customFilter::class,
            ],
            array_map(
                static fn (FilterInterface $filter): string => $filter::class,
                $config->getFilters(),
            )
        );
    }

    public function testWithIgnoredClasses(): void
    {
        $config = new Config();
        $ignoredClasses = ['FirstClass', 'SecondClass', 'SecondClass', 'ThirdClass'];

        // @phpstan-ignore argument.type, argument.type, argument.type, argument.type
        $config->withIgnoredClasses(...$ignoredClasses);

        self::assertCount(3, $classes = $config->getIgnoredClasses());
        self::assertSame(array_unique($classes), $classes);
    }

    public function testWithIgnoredAttributes(): void
    {
        $config = new Config();
        $ignoredAttributes = ['FirstAttribute', 'SecondAttribute', 'SecondAttribute', 'ThirdAttribute'];

        // @phpstan-ignore argument.type, argument.type, argument.type, argument.type
        $config->withIgnoredAttributes(...$ignoredAttributes);

        self::assertCount(3, $attributes = $config->getIgnoredAttributes());
        self::assertSame(array_unique($attributes), $attributes);
    }

    public function testWithSetsSymfony(): void
    {
        $config = new Config();

        $config->withSets(symfony: true);

        self::assertCount(3, $filters = $config->getFilters());
        self::assertInstanceOf(AutoconfigureTagAttributeFilter::class, $filters[2]);
        self::assertCount(3, $config->getIgnoredClasses());
        self::assertCount(2, $config->getIgnoredAttributes());
    }

    public function testWithSetsDoctrine(): void
    {
        $config = new Config();

        $config->withSets(doctrine: true);

        self::assertSame(
            ['Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener'],
            $config->getIgnoredAttributes()
        );
    }

    public function testWithSetsTwig(): void
    {
        $config = new Config();

        $config->withSets(twig: true);

        self::assertSame(['Twig\Extension\ExtensionInterface'], $config->getIgnoredClasses());
    }

    public function testWithSetsPhpunit(): void
    {
        $config = new Config();

        $config->withSets(phpunit: true);

        self::assertSame(['PHPUnit\Framework\TestCase'], $config->getIgnoredClasses());
    }

    public function testWithSetsCodeception(): void
    {
        $config = new Config();

        $config->withSets(codeception: true);

        self::assertInstanceOf(CodeceptionTestClassFilter::class, $config->getFilters()[2] ?? null);
    }

    public function testWithCustomSet(): void
    {
        $config = new Config();
        $customSet = new class implements SetInterface {
            #[\Override]
            public function __invoke(Config $config): void
            {
                // @phpstan-ignore argument.type
                $config->withIgnoredClasses('FirstClass');
                // @phpstan-ignore argument.type
                $config->withIgnoredAttributes('FirstAttribute');
            }
        };

        $config->withSet($customSet);

        self::assertCount(2, $config->getFilters());
        self::assertSame(['FirstClass'], $config->getIgnoredClasses());
        self::assertSame(['FirstAttribute'], $config->getIgnoredAttributes());
    }

    private static function assertFinderPropertySame(Finder $finder, string $property, mixed $expected): void
    {
        $property = new \ReflectionProperty($finder, $property);

        self::assertSame($expected, $property->getValue($finder));
    }
}
