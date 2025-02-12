<?php

declare(strict_types=1);

namespace DBublik\UnusedClass;

use DBublik\UnusedClass\Filter\AttributeFilter;
use DBublik\UnusedClass\Filter\AutoconfigureTagAttributeFilter;
use DBublik\UnusedClass\Filter\ClassFilter;
use DBublik\UnusedClass\Filter\CodeceptionTestFilter;
use DBublik\UnusedClass\Filter\FilterInterface;
use Symfony\Component\Finder\Finder;

final class Config
{
    private Finder $finder;
    private string $cacheDir;

    /**
     * @var array<class-string, FilterInterface>
     */
    private array $filters = [];

    /**
     * @var array<class-string>
     */
    private array $ignoredClasses = [];

    /**
     * @var array<class-string>
     */
    private array $ignoredAttributes = [];

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
     * @return array<class-string, FilterInterface>
     */
    public function getFilters(): array
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
                    sprintf('Filter %s must implement %s', $filter::class, FilterInterface::class),
                );
            }

            $this->addFilter($filter);
        }

        return $this;
    }

    private function addFilter(FilterInterface $filter): void
    {
        $this->filters[$filter::class] = $filter;
    }

    /**
     * @return array<class-string>
     */
    public function getIgnoredClasses(): array
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
     * @return array<class-string>
     */
    public function getIgnoredAttributes(): array
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
        if ($symfony) {
            $this->addFilter(new AutoconfigureTagAttributeFilter());
            $this->withIgnoredClasses([
                'Symfony\Component\EventDispatcher\EventSubscriberInterface',
                'Symfony\Component\Form\FormTypeExtensionInterface',
                'Symfony\Component\Validator\ConstraintValidatorInterface',
            ]);
            $this->withIgnoredAttributes([
                'Symfony\Component\Console\Attribute\AsCommand',
                'Symfony\Component\Routing\Attribute\Route',
            ]);
        }

        if ($doctrine) {
            $this->withIgnoredAttributes(['Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener']);
        }

        if ($twig) {
            $this->withIgnoredClasses(['Twig\Extension\ExtensionInterface']);
        }

        if ($phpunit) {
            $this->withIgnoredClasses(['PHPUnit\Framework\TestCase']);
        }

        if ($codeception) {
            $this->addFilter(new CodeceptionTestFilter());
            $this->withIgnoredClasses(['Codeception\Module']);
        }

        return $this;
    }
}
