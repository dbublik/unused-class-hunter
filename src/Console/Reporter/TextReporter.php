<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Console\Reporter;

use DBublik\UnusedClass\ValueObject\FileInformation;

final readonly class TextReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'txt';
    }

    /**
     * @param list<FileInformation> $unusedClasses
     */
    public function generate(array $unusedClasses): string
    {
        if (empty($unusedClasses)) {
            return '<info>Success! The hunt is over — no unused classes found.</info>' . PHP_EOL . PHP_EOL;
        }

        $output = '';
        foreach ($unusedClasses as $unusedClass) {
            $output .= $unusedClass->getRelativeFile() . PHP_EOL;
        }

        $output .= PHP_EOL;
        $output .= sprintf('<error>The hunt is over! %s unused classes detected.</error>', count($unusedClasses));

        return $output . PHP_EOL . PHP_EOL;
    }
}
