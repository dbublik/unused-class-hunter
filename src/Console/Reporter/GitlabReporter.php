<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

final readonly class GitlabReporter implements ReporterInterface
{
    #[\Override]
    public function getFormat(): string
    {
        return 'gitlab';
    }

    /**
     * @return non-empty-string
     */
    #[\Override]
    public function generate(ReportSummary $summary): string
    {
        $report = [];

        foreach ($summary->unusedClasses as $unusedClass) {
            $report[] = [
                'description' => \sprintf('The %s class is not used.', $unusedClass->getName()),
                'fingerprint' => md5($unusedClass->getFile()),
                'severity' => 'minor',
                'location' => [
                    'path' => $unusedClass->getFile(),
                    'lines' => [
                        'begin' => $unusedClass->getStartLine(),
                    ],
                ],
            ];
        }

        return json_encode($report, JSON_THROW_ON_ERROR);
    }
}
