<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Console;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Console\ConfigurationResolver;
use DBublik\UnusedClassHunter\Console\Reporter\GitlabReporter;
use DBublik\UnusedClassHunter\Console\Reporter\TextReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConfigurationResolver::class)]
final class ConfigurationResolverTest extends TestCase
{
    #[DataProvider('provideGetConfigException')]
    public function testGetConfigException(mixed $config, string $exceptionMessage): void
    {
        $resolver = new ConfigurationResolver(['config' => $config]);

        $this->expectExceptionObject(
            new \InvalidArgumentException($exceptionMessage)
        );

        $resolver->getConfig();
    }

    /**
     * @return iterable<array{0: mixed, 1: string}>
     */
    public static function provideGetConfigException(): iterable
    {
        yield [
            1234234,
            'Config file must be a string, integer given',
        ];

        yield [
            [],
            'Config file must be a string, array given',
        ];

        yield [
            __DIR__ . '/../Fixtures/Not/Found',
            'The config file does not exist.',
        ];

        yield [
            $configFile = __DIR__ . '/../Fixtures/Console/ConfigurationResolver/bad-config-file.php',
            \sprintf('The config file: "%s" does not return a "%s" instance.', $configFile, Config::class),
        ];
    }

    public function testGetConfigExceptionNotReadable(): void
    {
        $config = sys_get_temp_dir() . '/' . uniqid('unused-class-hunter-test_', true);
        touch($config);

        try {
            chmod($config, 0o00);
            $resolver = new ConfigurationResolver(['config' => $config]);

            $this->expectExceptionObject(
                new \InvalidArgumentException(\sprintf('Cannot read config file "%s".', $config))
            );

            $resolver->getConfig();
        } finally {
            chmod($config, 0o666);
            unlink($config);
        }
    }

    public function testGetConfig(): void
    {
        $resolver = new ConfigurationResolver(
            options: ['config' => __DIR__ . '/../Fixtures/Console/ConfigurationResolver/.unused-class-hunter.dist.php'],
        );

        $config = $resolver->getConfig();

        self::assertSame([self::class], $config->getIgnoredClasses());
    }

    /**
     * @param null|non-empty-string $rootDirectory
     */
    #[DataProvider('provideGetConfigDefault')]
    public function testGetConfigDefault(?string $rootDirectory, bool $isDefaultConfig): void
    {
        $resolver = new ConfigurationResolver(
            options: [],
            rootDirectory: $rootDirectory,
        );

        $config = $resolver->getConfig();

        self::assertSame($isDefaultConfig, [self::class] !== $config->getIgnoredClasses());
    }

    /**
     * @return iterable<array{0: null|non-empty-string, 1: bool}>
     */
    public static function provideGetConfigDefault(): iterable
    {
        yield [
            null,
            true,
        ];

        yield [
            __DIR__ . '/../Fixtures/Console/ConfigurationResolver/Bad/Root/Directory',
            true,
        ];

        yield [
            __DIR__ . '/../Fixtures/Console/ConfigurationResolver',
            false,
        ];
    }

    public function testGetReporterException(): void
    {
        $resolver = new ConfigurationResolver(['format' => []]);

        $this->expectExceptionObject(
            new \InvalidArgumentException('Format must be a string, array given')
        );

        $resolver->getReporter();
    }

    public function testGetReporter(): void
    {
        $resolver = new ConfigurationResolver(['format' => 'gitlab']);

        $reporter = $resolver->getReporter();

        self::assertSame(GitlabReporter::class, $reporter::class);
    }

    public function testGetReporterDefault(): void
    {
        $resolver = new ConfigurationResolver([]);

        $reporter = $resolver->getReporter();

        self::assertSame(TextReporter::class, $reporter::class);
    }

    #[DataProvider('provideIsDeletable')]
    public function testIsDeletable(bool $expectedIsDeletable, mixed $deleteOption): void
    {
        $resolver = new ConfigurationResolver(['delete' => $deleteOption]);

        $isDeletable = $resolver->isDeletable();

        self::assertSame($expectedIsDeletable, $isDeletable);
    }

    /**
     * @return iterable<array{0: bool, 1: mixed}>
     */
    public static function provideIsDeletable(): iterable
    {
        yield [
            false,
            [],
        ];

        yield [
            false,
            false,
        ];

        yield [
            false,
            1,
        ];

        yield [
            true,
            true,
        ];
    }

    public function testIsDeletableDefault(): void
    {
        $resolver = new ConfigurationResolver([]);

        $isDeletable = $resolver->isDeletable();

        self::assertFalse($isDeletable);
    }
}
