<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/var/cache/.php-cs-fixer.json')
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude(['var'])
            ->ignoreDotFiles(false)
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP83Migration' => true,
        '@PHP82Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'attribute_empty_parentheses' => true,
        'concat_space' => ['spacing' => 'one'],
        'get_class_to_class_keyword' => true,
        'modernize_strpos' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_param_order' => true,
    ]);
