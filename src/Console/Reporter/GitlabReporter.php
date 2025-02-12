<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Console\Reporter;

use DBublik\UnusedClass\ValueObject\FileInformation;

final readonly class GitlabReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'gitlab';
    }

    /**
     * @param list<FileInformation> $unusedClasses
     */
    public function generate(array $unusedClasses): string
    {
        $report = [];

        foreach ($unusedClasses as $unusedClass) {
            $report[] = [
                'description' => sprintf('The %s class is not used.', $unusedClass->getClassName()),
                'fingerprint' => md5($unusedClass->getFile()),
                'severity' => 'minor',
                'location' => [
                    'path' => $unusedClass->getFile(),
                    'lines' => [
                        'begin' => 0,
                    ],
                ],
            ];
        }

        return json_encode($report, JSON_THROW_ON_ERROR);
    }
}
