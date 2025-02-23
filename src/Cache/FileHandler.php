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

        $json = @file_get_contents($this->file);

        try {
            /**
             * @var array<string, mixed> $content
             *
             * @infection-ignore-all
             */
            $content = json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return $content;
    }

    public function write(\JsonSerializable $value): void
    {
        $directory = \dirname($this->file);

        // @infection-ignore-all
        if (!is_dir($directory) && !@mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new \RuntimeException(\sprintf('Failed to create "%s".', $directory));
        }

        if (false === @file_put_contents($this->file, json_encode($value, JSON_THROW_ON_ERROR))) {
            throw new \RuntimeException(\sprintf('Failed to write to "%s".', $this->file));
        }
    }
}
