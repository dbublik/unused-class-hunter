<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Cache;

use DBublik\UnusedClassHunter\ValueObject\AbstractFileNode;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\FileNode;

final readonly class Cache
{
    public function __construct(
        private string $cacheDir,
    ) {}

    /**
     * @param non-empty-string $file
     */
    public function get(string $file): ?AbstractFileNode
    {
        $cacheFile = $this->getFileName($file);

        if (!file_exists($cacheFile)) {
            return null;
        }

        if (false === $json = file_get_contents($cacheFile)) {
            return null;
        }

        try {
            $data = (array) json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (\array_key_exists('class', $data)) {
            return ClassNode::fromData($data);
        }

        return FileNode::fromData($data);
    }

    /**
     * @param non-empty-string $file
     */
    public function set(string $file, AbstractFileNode $fileNode): void
    {
        $cacheFile = $this->getFileName($file);
        $directory = \dirname($cacheFile);

        if (
            !file_exists($directory)
            && !is_dir($directory) && !@mkdir($directory, 0o777, true) && !is_dir($directory)
        ) {
            throw new \RuntimeException(\sprintf('Failed to create "%s".', $directory));
        }

        if (!touch($cacheFile)) {
            throw new \RuntimeException(\sprintf('Failed to touch "%s".', $cacheFile));
        }

        if (false === file_put_contents($cacheFile, json_encode($fileNode, JSON_THROW_ON_ERROR))) {
            throw new \RuntimeException(\sprintf('Failed to write to "%s".', $cacheFile));
        }
    }

    private function getFileName(string $file): string
    {
        return $this->cacheDir . '/classes/' . hash_file('sha256', $file);
    }
}
