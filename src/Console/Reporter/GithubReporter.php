<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

final readonly class GithubReporter implements ReporterInterface
{
    /**
     * @return non-empty-string
     */
    #[\Override]
    public function getFormat(): string
    {
        return 'github';
    }

    /**
     * @return non-empty-string
     */
    #[\Override]
    public function generate(ReportSummary $summary): string
    {
        $errors = [];

        foreach ($summary->unusedClasses as $unusedClass) {
            $error = \sprintf(
                '::error file=%s,line=%d::The %s class is not used%s',
                $unusedClass->getFile(),
                $unusedClass->getStartLine(),
                $unusedClass->getName(),
                $summary->isDeletable ? ' and deleted' : ''
            );
            $error .= PHP_EOL;

            $errors[] = $error;
        }

        return '::group::Hunter report' . PHP_EOL
            . implode('', $errors)
            . '::endgroup::' . PHP_EOL;
    }
}
