<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console\Reporter;

use DBublik\UnusedClassHunter\ValueObject\ClassNode;

final readonly class ReportSummary
{
    public function __construct(
        /** @var list<ClassNode> */
        public array $unusedClasses,
        public int $duration = 0,
        public int $memory = 0,
        public bool $isDecoratedOutput = true,
    ) {}
}
