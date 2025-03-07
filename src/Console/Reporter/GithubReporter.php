<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

/**
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
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
            $errors[] = \sprintf(
                '::error file=%s,line=%d::The %s class is not used%s',
                $unusedClass->getFile(),
                $unusedClass->getStartLine(),
                $unusedClass->getName(),
                $summary->isDeletable ? ' and deleted' : ''
            );
        }

        return '::group::Hunter report' . PHP_EOL
            . implode(PHP_EOL, $errors) . PHP_EOL
            . '::endgroup::' . PHP_EOL;
    }
}
