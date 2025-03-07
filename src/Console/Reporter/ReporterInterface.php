<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

interface ReporterInterface
{
    /**
     * @return non-empty-string
     */
    public function getFormat(): string;

    /**
     * @return non-empty-string
     */
    public function generate(ReportSummary $summary): string;
}
