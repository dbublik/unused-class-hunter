<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;

final readonly class TextReporter implements ReporterInterface
{
    #[\Override]
    public function getFormat(): string
    {
        return 'text';
    }

    /**
     * @return non-empty-string
     */
    #[\Override]
    public function generate(ReportSummary $summary): string
    {
        if ([] === $summary->unusedClasses) {
            $output = '<info>Success! The hunt is over — no unused classes found.</info>';
        } else {
            $output = '';
            foreach ($summary->unusedClasses as $unusedClass) {
                $output .= '<comment>' . $this->getRelativeFile($unusedClass) . '</comment>' . PHP_EOL;
            }

            $output .= PHP_EOL;
            $output .= \sprintf(
                '<error>The hunt is over! %s unused classes detected.</error>',
                \count($summary->unusedClasses)
            );
        }

        $output .= PHP_EOL . PHP_EOL;
        $output .= $this->getServiceInformation($summary);

        return $output . PHP_EOL . PHP_EOL;
    }

    private function getRelativeFile(ClassNode $unusedClass): string
    {
        return str_replace(getcwd() . \DIRECTORY_SEPARATOR, '', $unusedClass->getFile());
    }

    private function getServiceInformation(ReportSummary $summary): string
    {
        return \sprintf(
            'Duration %.3f seconds, %.2f MB memory used.',
            $summary->duration,
            $summary->memory / 1_024 / 1_024
        );
    }
}
