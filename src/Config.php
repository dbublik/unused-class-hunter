<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

use DBublik\UnusedClassHunter\Filter\AttributeFilter;
use DBublik\UnusedClassHunter\Filter\ClassFilter;
use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\Sets\AbstractSet;
use DBublik\UnusedClassHunter\Sets\CodeceptionSet;
use DBublik\UnusedClassHunter\Sets\DoctrineSet;
use DBublik\UnusedClassHunter\Sets\PhpunitSet;
use DBublik\UnusedClassHunter\Sets\SymfonySet;
use DBublik\UnusedClassHunter\Sets\TwigSet;
use Symfony\Component\Finder\Finder;

final class Config
{
    private Finder $finder;
    private string $cacheDir;

    /**
     * @var iterable<class-string, FilterInterface>
     */
    private iterable $filters = [];

    /**
     * @var iterable<class-string>
     */
    private iterable $ignoredClasses = [];

    /**
     * @var iterable<class-string>
     */
    private iterable $ignoredAttributes = [];

    public function __construct()
    {
        $this->finder = Finder::create()->in(getcwd());
        $this->cacheDir = sys_get_temp_dir();

        $this->addFilter(new ClassFilter());
        $this->addFilter(new AttributeFilter());
    }

    public function getFinder(): Finder
    {
        return $this->finder
            ->exclude(['var', 'vendor'])
            ->sortByName()
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
     * @return iterable<class-string, FilterInterface>
     */
    public function getFilters(): iterable
    {
        return $this->filters;
    }

    /**
     * @param iterable<FilterInterface> $filters
     */
    public function withFilters(iterable $filters): self
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof FilterInterface) {
                throw new \InvalidArgumentException(
                    \sprintf('Filter %s must implement %s', $filter::class, FilterInterface::class),
                );
            }

            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredClasses(): iterable
    {
        return array_unique($this->ignoredClasses);
    }

    /**
     * @param iterable<class-string> $classes
     */
    public function withIgnoredClasses(iterable $classes): self
    {
        $this->ignoredClasses = array_merge($this->ignoredClasses, iterator_to_array($classes));

        return $this;
    }

    /**
     * @return iterable<class-string>
     */
    public function getIgnoredAttributes(): iterable
    {
        return array_unique($this->ignoredAttributes);
    }

    /**
     * @param iterable<class-string> $attributes
     */
    public function withIgnoredAttributes(iterable $attributes): self
    {
        $this->ignoredAttributes = array_merge($this->ignoredAttributes, iterator_to_array($attributes));

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
            if ($isEnabled) {
                $this->withSet($sets[$key]);
            }
        }

        return $this;
    }

    public function withSet(AbstractSet $set): void
    {
        $this->withFilters($set->getFilters());
        $this->withIgnoredClasses($set->getIgnoredClasses());
        $this->withIgnoredAttributes($set->getIgnoredAttributes());
    }

    private function addFilter(FilterInterface $filter): void
    {
        $this->filters[$filter::class] = $filter;
    }
}
