<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;

final readonly class TextReporter implements ReporterInterface
{
    #[\Override]
    public function getFormat(): string
    {
        return 'txt';
    }

    /**
     * @param list<ClassNode> $unusedClasses
     *
     * @return non-empty-string
     */
    #[\Override]
    public function generate(array $unusedClasses): string
    {
        if ([] === $unusedClasses) {
            return '<info>Success! The hunt is over — no unused classes found.</info>' . PHP_EOL . PHP_EOL;
        }

        $output = '';
        foreach ($unusedClasses as $unusedClass) {
            $output .= $this->getRelativeFile($unusedClass) . PHP_EOL;
        }

        $output .= PHP_EOL;
        $output .= \sprintf('<error>The hunt is over! %s unused classes detected.</error>', \count($unusedClasses));

        return $output . PHP_EOL . PHP_EOL;
    }

    private function getRelativeFile(ClassNode $unusedClass): string
    {
        return str_replace(getcwd() . \DIRECTORY_SEPARATOR, '', $unusedClass->getFile());
    }
}
