<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;
use Symfony\Component\Finder\Finder;

return (new Config())
    ->setFinder(Finder::create()->in(__DIR__ . '/empty-directory'))
    ->setCacheDir(sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true))
    ->withBootstrapFiles(__DIR__ . '/bootstrap-file.php');
