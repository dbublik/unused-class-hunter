<?php

namespace DBublik\UnusedClass\Cache;

use DBublik\UnusedClass\ValueObject\FileInformation;
use JsonException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class Cache
{
    public function __construct(
        private string $cacheDir,
        private Filesystem $filesystem = new Filesystem(),
    ) {}

    public function get(string $file): ?FileInformation
    {
        $cacheFile = $this->getFileName($file);

        if (!file_exists($cacheFile)) {
            return null;
        }

        if (false === $json = file_get_contents($cacheFile)) {
            return null;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return FileInformation::fromData($data);
    }

    public function set(string $file, FileInformation $information): void
    {
        $filePath = $this->getFileName($file);
        $dir = dirname($filePath);

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir);
        }

        $this->filesystem->touch($filePath);

        file_put_contents($filePath, json_encode($information, JSON_THROW_ON_ERROR));
    }

    private function getFileName(string $file): string
    {
        return $this->cacheDir . '/classes/' . hash_file('sha256', $file);
    }
}
