<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Console;

use DBublik\UnusedClassHunter\Config;
use DBublik\UnusedClassHunter\Console\Reporter\ReporterInterface;
use DBublik\UnusedClassHunter\Console\Reporter\ReportFactory;
use DBublik\UnusedClassHunter\Console\Reporter\TextReporter;

final readonly class ConfigurationResolver
{
    private string $rootDirectory;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private array $options,
        ?string $rootDirectory = null,
    ) {
        // @phpstan-ignore assign.propertyType
        $this->rootDirectory = $rootDirectory ?? getcwd();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getConfig(): Config
    {
        $configFile = $this->options['config'] ?? null;

        if (null !== $configFile) {
            if (!\is_string($configFile)) {
                throw new \InvalidArgumentException(
                    \sprintf('Config file must be a string, %s given', \gettype($configFile))
                );
            }
            if (!file_exists($configFile) || !is_readable($configFile)) {
                throw new \InvalidArgumentException(
                    \sprintf('Cannot read config file "%s".', $configFile)
                );
            }

            $config = include $configFile;

            if (!$config instanceof Config) {
                throw new \InvalidArgumentException(
                    \sprintf('The config file: "%s" does not return a "%s" instance.', $configFile, Config::class)
                );
            }

            return $config;
        }

        $supposedConfigFiles = [
            $this->rootDirectory . '/.unused-class-hunter.php',
            $this->rootDirectory . '/.unused-class-hunter.dist.php',
        ];

        foreach ($supposedConfigFiles as $supposedConfigFile) {
            if (!file_exists($supposedConfigFile)) {
                continue;
            }

            $config = include $supposedConfigFile;

            if ($config instanceof Config) {
                return $config;
            }
        }

        return new Config();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getReporter(): ReporterInterface
    {
        $format = $this->options['format'] ?? (new TextReporter())->getFormat();

        if (!\is_string($format)) {
            throw new \InvalidArgumentException(\sprintf('Format must be a string, %s given', \gettype($format)));
        }

        return (new ReportFactory())->getReporter($format);
    }

    public function isDeletable(): bool
    {
        return true === ($this->options['delete'] ?? false);
    }
}
