<?php

declare(strict_types=1);

namespace DBublik\UnusedClass\Console\Reporter;

final readonly class ReportFactory
{
    /**
     * @var ReporterInterface[]
     */
    private array $reporters;

    public function __construct()
    {
        $this->reporters = [
            new TextReporter(),
            new GitlabReporter(),
        ];
    }

    public function getReporter(string $format): ReporterInterface
    {
        foreach ($this->reporters as $reporter) {
            if ($format === $reporter->getFormat()) {
                return $reporter;
            }
        }

        throw new \InvalidArgumentException('Unsupported format.');
    }
}
