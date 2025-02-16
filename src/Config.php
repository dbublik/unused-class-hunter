<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\Sets\CodeceptionSet;
use DBublik\UnusedClassHunter\Sets\DoctrineSet;
use DBublik\UnusedClassHunter\Sets\PhpunitSet;
use DBublik\UnusedClassHunter\Sets\SetInterface;
use DBublik\UnusedClassHunter\Sets\SymfonySet;
use DBublik\UnusedClassHunter\Sets\TwigSet;
use Symfony\Component\Finder\Finder;

final class Config
{
    private Finder $finder;
    private string $cacheDir;

    /**
     * @var list<string>
     */
    private array $bootstrapFiles = [];

    /**
     * @var array<class-string<FilterInterface>, FilterInterface>
     */
    private array $filters = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $ignoredClasses = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $ignoredAttributes = [];

    public function __construct()
    {
        $this->finder = Finder::create()->in((string) getcwd());
        $this->cacheDir = sys_get_temp_dir() . '/unused-class-hunter';
        $this->withFilters(new ClassFilter(), new AttributeFilter());
    }

    public function getFinder(): Finder
    {
        return $this->finder
            ->exclude(['var', 'vendor'])
            ->sortByName()
            ->name('*.php')
            ->files();
    }

    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    public function setCacheDir(string $cacheDir): self
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getBootstrapFiles(): array
    {
        return $this->bootstrapFiles;
    }

    public function withBootstrapFiles(string ...$bootstrapFiles): self
    {
        foreach ($bootstrapFiles as $bootstrapFile) {
            if (!file_exists($bootstrapFile) || !is_file($bootstrapFile)) {
                throw new \InvalidArgumentException(\sprintf('Bootstrap file "%s" does not exist', $bootstrapFile));
            }

            $this->bootstrapFiles[] = $bootstrapFile;
        }

        return $this;
    }

    /**
     * @return list<FilterInterface>
     */
    public function getFilters(): array
    {
        return array_values($this->filters);
    }

    public function withFilters(FilterInterface ...$filters): self
    {
        foreach ($filters as $filter) {
            $this->filters[$filter::class] = $filter;
        }

        return $this;
    }

    /**
     * @return list<class-string>
     */
    public function getIgnoredClasses(): array
    {
        return array_values($this->ignoredClasses);
    }

    /**
     * @param class-string ...$classes
     */
    public function withIgnoredClasses(string ...$classes): self
    {
        foreach ($classes as $class) {
            $this->ignoredClasses[$class] = $class;
        }

        return $this;
    }

    /**
     * @return list<class-string>
     */
    public function getIgnoredAttributes(): array
    {
        return array_values($this->ignoredAttributes);
    }

    /**
     * @param class-string ...$attributes
     */
    public function withIgnoredAttributes(string ...$attributes): self
    {
        foreach ($attributes as $attribute) {
            $this->ignoredAttributes[$attribute] = $attribute;
        }

        return $this;
    }

    public function withSets(
        bool $symfony = false,
        bool $doctrine = false,
        bool $twig = false,
        bool $phpunit = false,
        bool $codeception = false,
    ): self {
        $sets = [
            new SymfonySet(),
            new DoctrineSet(),
            new TwigSet(),
            new PhpunitSet(),
            new CodeceptionSet(),
        ];

        foreach (\func_get_args() as $key => $isEnabled) {
            if (\array_key_exists($key, $sets) && \is_bool($isEnabled) && $isEnabled) {
                $this->withSet($sets[$key]);
            }
        }

        return $this;
    }

    public function withSet(SetInterface $set): self
    {
        $set($this);

        return $this;
    }
}
