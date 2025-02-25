<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Fixtures\UnusedClassFinder;

/**
 * @api
 */
final readonly class ExampleIgnoredTestClass
{
    public const USED_CLASS = ExampleUsedTestClass::class;
}
