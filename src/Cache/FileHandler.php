<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Cache;

final readonly class FileHandler
{
    public function __construct(
        /** @var non-empty-string */
        private string $file,
    ) {}

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->file;
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function read(): ?array
    {
        if (!file_exists($this->file)) {
            return null;
        }

        if (false === $json = file_get_contents($this->file)) {
            return null;
        }

        try {
            return (array) json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    public function write(\JsonSerializable $value): void
    {
        $directory = \dirname($this->file);

        if (
            !file_exists($directory)
            && !is_dir($directory) && !@mkdir($directory, 0o777, true) && !is_dir($directory)
        ) {
            throw new \RuntimeException(\sprintf('Failed to create "%s".', $directory));
        }

        if (!touch($this->file)) {
            throw new \RuntimeException(\sprintf('Failed to touch "%s".', $this->file));
        }

        if (false === file_put_contents($this->file, json_encode($value, JSON_THROW_ON_ERROR))) {
            throw new \RuntimeException(\sprintf('Failed to write to "%s".', $this->file));
        }
    }
}
