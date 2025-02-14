<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Filter\ApiTagFilter;
use Symfony\Component\Finder\Finder;

return (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude(['tools', 'var', 'vendor'])
            ->append([__DIR__ . '/bin/unused-class-hunter'])
    )
    ->setCacheDir(__DIR__ . '/var/cache/unused-class-hunter')
    ->withFilters(new ApiTagFilter())
    ->withSets(
        symfony: true,
        doctrine: true,
        twig: true,
        phpunit: true,
        codeception: true
    );
