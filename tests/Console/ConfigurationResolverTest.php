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
            $configFile = __DIR__ . '/../Fixtures/Not/Found',
            \sprintf('Cannot read config file "%s".', $configFile),
        ];

        yield [
            $configFile = __DIR__ . '/../Fixtures/Console/ConfigurationResolver/bad-config-file.php',
            \sprintf('The config file: "%s" does not return a "%s" instance.', $configFile, Config::class),
        ];
    }

    /**
     * @param list<class-string> $ignoredClasses
     */
    #[DataProvider('provideGetConfig')]
    public function testGetConfig(?string $rootDirectory, array $ignoredClasses): void
    {
        $resolver = new ConfigurationResolver(
            options: [],
            rootDirectory: __DIR__ . '/../Fixtures/Console/ConfigurationResolver',
        );

        $config = $resolver->getConfig();

        self::assertSame([self::class], $config->getIgnoredClasses());
    }

    /**
     * @return iterable<array{0: null|string, 1: list<class-string>}>
     */
    public static function provideGetConfig(): iterable
    {
        yield [
            null,
            [],
        ];

        yield [
            __DIR__ . '/../Fixtures/Console/ConfigurationResolver/Bad/Root/Directory',
            [],
        ];

        yield [
            __DIR__ . '/../Fixtures/Console/ConfigurationResolver',
            [self::class],
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
}
