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
        $output = '';

        if ([] === $summary->unusedClasses) {
            $result = 'Success! The hunt is over — no unused classes found.';

            $output = $summary->isDecoratedOutput ? \sprintf('<info>%s</info>', $result) : $result;
        } else {
            foreach ($summary->unusedClasses as $unusedClass) {
                $file = $this->getRelativeFile($unusedClass);

                if ($summary->isDeletable) {
                    $output .= \sprintf('[-] %s', $file);
                } else {
                    $output .= $summary->isDecoratedOutput ? \sprintf('<comment>%s</comment>', $file) : $file;
                }

                $output .= PHP_EOL;
            }

            $output .= PHP_EOL;

            $result = \sprintf(
                'The hunt is over! %s unused classes %s.',
                \count($summary->unusedClasses),
                $summary->isDeletable ? 'deleted' : 'detected',
            );
            $output .= $summary->isDecoratedOutput ? \sprintf('<error>%s</error>', $result) : $result;
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
