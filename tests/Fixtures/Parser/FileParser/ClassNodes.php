<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Fixtures\Parser\FileParser;

use DBublik\UnusedClassHunter\Tests\Parser\FileParserTest;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ClassNodes extends \stdClass
{
    /** @var null|class-string<FileParserTest> */
    public ?string $testClass = null;
}
