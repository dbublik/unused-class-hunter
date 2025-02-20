<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests;

use DBublik\UnusedClassHunter\ComposerResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ComposerResolver::class)]
final class ComposerResolverTest extends TestCase
{
    public function testGetVersionNoFound(): void
    {
        $resolver = new ComposerResolver(__DIR__ . '/Fixtures/Not/Found');

        $version = $resolver->getVersion('package-name');

        self::assertNull($version);
    }

    #[DataProvider('provideGetVersionException')]
    public function testGetVersionException(string $installedFile, string $exceptionMessage): void
    {
        $resolver = new ComposerResolver($installedFile);

        $this->expectExceptionObject(
            new \RuntimeException($exceptionMessage)
        );

        $resolver->getVersion('not-exists-package-name');
    }

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public static function provideGetVersionException(): iterable
    {
        yield [
            $file = __DIR__ . '/Fixtures',
            \sprintf('Could not read content from "%s"', $file),
        ];

        yield [
            __DIR__ . '/../vendor/composer/installed.json',
            'Could not find current package',
        ];
    }

    public function testGetVersion(): void
    {
        $resolver = new ComposerResolver(__DIR__ . '/../vendor/composer/installed.json');

        $version = $resolver->getVersion('nikic/php-parser');

        self::assertNotNull($version);
        self::assertStringStartsWith('v5', $version);
    }
}
