<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

final readonly class UnusedClass
{
    public function unusedMethod(): never
    {
        exit;
    }
}
