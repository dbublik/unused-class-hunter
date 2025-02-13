<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\Sets\AbstractSet;
use DBublik\UnusedClassHunter\Sets\CodeceptionSet;
use DBublik\UnusedClassHunter\Sets\DoctrineSet;
use DBublik\UnusedClassHunter\Sets\PhpunitSet;
use DBublik\UnusedClassHunter\Sets\SymfonySet;
use DBublik\UnusedClassHunter\Sets\TwigSet;
use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use DBublik\UnusedClassHunter\ValueObject\ParseInformation;
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
    public function testCreate(): void
    {
        $config = new Config();

        $finder = $config->getFinder();
        self::assertFinderPropertySame($finder, 'dirs', [getcwd()]);
        self::assertFinderPropertySame($finder, 'exclude', ['var', 'vendor']);
        self::assertFinderPropertySame($finder, 'sort', SortableIterator::SORT_BY_NAME);
        self::assertFinderPropertySame($finder, 'mode', FileTypeFilterIterator::ONLY_FILES);
        self::assertFinderPropertySame($finder, 'iterators', []);
        self::assertSame(sys_get_temp_dir(), $config->getCacheDir());
        self::assertCount(2, $filters = $config->getFilters());
        self::assertArrayHasKey(ClassFilter::class, $filters);
        self::assertArrayHasKey(AttributeFilter::class, $filters);
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

    public function testWithFilters(): void
    {
        $config = new Config();
        $customFilter = new class implements FilterInterface {
            public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
            {
                return false;
            }
        };

        $config->withFilters([$customFilter]);

        self::assertCount(3, $filters = $config->getFilters());
        self::assertArrayHasKey($customFilter::class, $filters);
    }

    public function testExceptionWithFilters(): void
    {
        $config = new Config();
        $badFilter = new class {};

        $this->expectExceptionObject(
            new \InvalidArgumentException(
                \sprintf('Filter %s must implement %s', $badFilter::class, FilterInterface::class),
            )
        );

        $config->withFilters([$badFilter]);
    }

    public function testWithIgnoredClasses(): void
    {
        $config = new Config();
        $ignoredClasses = ['FirstClass', 'SecondClass', 'SecondClass', 'ThirdClass'];

        $config->withIgnoredClasses($ignoredClasses);

        self::assertCount(3, $classes = $config->getIgnoredClasses());
        self::assertSame(array_unique($ignoredClasses), $classes);
    }

    public function testWithIgnoredAttributes(): void
    {
        $config = new Config();
        $ignoredAttributes = ['FirstAttribute', 'SecondAttribute', 'SecondAttribute', 'ThirdAttribute'];

        $config->withIgnoredAttributes($ignoredAttributes);

        self::assertCount(3, $attributes = $config->getIgnoredAttributes());
        self::assertSame(array_unique($ignoredAttributes), $attributes);
    }

    public function testWithSymfonySet(): void
    {
        $config = new Config();

        $config->withSets(symfony: true);

        self::assertSet($config, new SymfonySet());
    }

    public function testWithDoctrineSet(): void
    {
        $config = new Config();

        $config->withSets(doctrine: true);

        self::assertSet($config, new DoctrineSet());
    }

    public function testWithTwigSet(): void
    {
        $config = new Config();

        $config->withSets(twig: true);

        self::assertSet($config, new TwigSet());
    }

    public function testWithPhpunitSet(): void
    {
        $config = new Config();

        $config->withSets(phpunit: true);

        self::assertSet($config, new PhpunitSet());
    }

    public function testWithCodeceptionSet(): void
    {
        $config = new Config();

        $config->withSets(codeception: true);

        self::assertSet($config, new CodeceptionSet());
    }

    public function testWithCustomSet(): void
    {
        $config = new Config();
        $customSet = new readonly class extends AbstractSet {
            public function getIgnoredClasses(): iterable
            {
                return ['FirstClass'];
            }

            public function getIgnoredAttributes(): iterable
            {
                return ['FirstAttribute'];
            }
        };

        $config->withSet($customSet);

        self::assertSet($config, $customSet);
    }

    private static function assertFinderPropertySame(Finder $finder, string $property, mixed $expected): void
    {
        $property = new \ReflectionProperty($finder, $property);

        self::assertSame($expected, $property->getValue($finder));
    }

    private static function assertSet(Config $config, AbstractSet $set): void
    {
        if (!empty($setFilters = $set->getFilters())) {
            self::assertCount(2 + \count($setFilters), $filters = $config->getFilters());

            foreach ($setFilters as $setFilter) {
                self::assertArrayHasKey($setFilter::class, $filters);
            }
        }

        if (!empty($setIgnoredClasses = $set->getIgnoredClasses())) {
            self::assertSame(
                iterator_to_array($setIgnoredClasses),
                iterator_to_array($config->getIgnoredClasses())
            );
        }

        if (!empty($setIgnoredAttributes = $set->getIgnoredAttributes())) {
            self::assertSame(
                iterator_to_array($setIgnoredAttributes),
                iterator_to_array($config->getIgnoredAttributes())
            );
        }
    }
}
