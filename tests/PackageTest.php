<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests;

use DBublik\UnusedClassHunter\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Package::class)]
final class PackageTest extends TestCase
{
    public function testGetVersion(): void
    {
        $version = Package::getVersion();

        self::assertSame(Package::VERSION, $version);
    }

    public function testGetComposerVersion(): void
    {
        $version = Package::getComposerVersion();

        self::assertNull($version);
    }
}
