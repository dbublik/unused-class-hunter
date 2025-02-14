<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

use DBublik\UnusedClassHunter\ValueObject\FileInformation;

final readonly class GitlabReporter implements ReporterInterface
{
    #[\Override]
    public function getFormat(): string
    {
        return 'gitlab';
    }

    /**
     * @param list<FileInformation> $unusedClasses
     */
    #[\Override]
    public function generate(array $unusedClasses): string
    {
        $report = [];

        foreach ($unusedClasses as $unusedClass) {
            $report[] = [
                'description' => \sprintf('The %s class is not used.', $unusedClass->getClassName()),
                'fingerprint' => md5($unusedClass->getFile()),
                'severity' => 'minor',
                'location' => [
                    'path' => $unusedClass->getFile(),
                    'lines' => [
                        'begin' => $unusedClass->getClassStartLine(),
                    ],
                ],
            ];
        }

        return json_encode($report, JSON_THROW_ON_ERROR);
    }
}
