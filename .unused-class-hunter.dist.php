<?php

declare(strict_types=1);

use DBublik\UnusedClass\Config;

return (new Config())
    ->setCacheDir(__DIR__ . '/var/cache/unused-class-hunter')
    ->withSets(
        symfony: true,
        doctrine: true,
        twig: true,
        phpunit: true,
        codeception: true
    );
