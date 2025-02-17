<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Fixtures\Parser\FileParser;

use DBublik\UnusedClassHunter\Tests\Parser\FileParserTest;
use DBublik\UnusedClassHunter\ValueObject\FileNode;

/**
 * @param class-string<FileNode> $name
 */
function exampleMethod(string $name): string
{
    return FileParserTest::class;
}
