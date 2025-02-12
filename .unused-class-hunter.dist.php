<?php

declare(strict_types=1);

use DBublik\UnusedClass\Config;
use Symfony\Component\Finder\Finder;

return (new Config())
    ->setCacheDir(__DIR__ . '/var/cache')
    ->setFinder(
        Finder::create()->in([__DIR__ . '/src'])
    )
    ->withSets(
        symfony: true,
        doctrine: true,
        twig: true,
        phpunit: true,
        codeception: true
    );
