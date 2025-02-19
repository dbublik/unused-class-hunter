<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;
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
        self::assertCount(2, $config->getFilters());
        self::assertHasFilter($config, ClassFilter::class);
        self::assertHasFilter($config, AttributeFilter::class);
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

        self::assertCount(3, $config->getFilters());
        self::assertHasFilter($config, $customFilter::class);
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

    public function testWithSets(): void
    {
        $config = new Config();

        $config->withSets(phpunit: true);

        self::assertCount(2, $config->getFilters());
        self::assertSame(['PHPUnit\Framework\TestCase'], $config->getIgnoredClasses());
        self::assertEmpty($config->getIgnoredAttributes());
    }

    public function testWithCustomSet(): void
    {
        $config = new Config();
        $customSet = new readonly class implements SetInterface {
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

    /**
     * @param class-string<FilterInterface> $filterClass
     */
    private static function assertHasFilter(Config $config, string $filterClass): void
    {
        $isExists = false;
        foreach ($config->getFilters() as $filter) {
            if ($filter instanceof $filterClass) {
                $isExists = true;

                break;
            }
        }

        self::assertTrue($isExists);
    }

    private static function assertFinderPropertySame(Finder $finder, string $property, mixed $expected): void
    {
        $property = new \ReflectionProperty($finder, $property);

        self::assertSame($expected, $property->getValue($finder));
    }
}
