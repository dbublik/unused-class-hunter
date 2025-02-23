<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter;

final readonly class ComposerResolver
{
    private const string INSTALLED_FILE_DEFAULT = __DIR__ . '/../../../composer/installed.json';

    public function __construct(
        private string $installedFile = self::INSTALLED_FILE_DEFAULT,
    ) {}

    /**
     * @return null|non-empty-string
     *
     * @throws \RuntimeException
     */
    public function getVersion(string $packageName): ?string
    {
        if (!file_exists($this->installedFile)) {
            return null;
        }

        $json = @file_get_contents($this->installedFile);

        try {
            /**
             * @var array<string, mixed> $installed
             *
             * @infection-ignore-all
             */
            $installed = json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \RuntimeException(\sprintf('Could not read content from "%s"', $this->installedFile));
        }

        /**
         * @var list<array{
         *     name: non-empty-string,
         *     version: non-empty-string,
         *     dist?: array{reference: string}
         * }> $packages
         */
        $packages = $installed['packages'] ?? $installed;

        foreach ($packages as $package) {
            if ($package['name'] !== $packageName) {
                continue;
            }

            $versionSuffix = '';
            if (isset($package['dist']['reference'])) {
                $versionSuffix = '#' . $package['dist']['reference'];
            }

            return $package['version'] . $versionSuffix;
        }

        throw new \RuntimeException('Could not find current package');
    }
}
