<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Cache;

final readonly class Signature implements \JsonSerializable
{
    public function __construct(
        /** @var non-empty-string */
        public string $phpVersion,
        /** @var non-empty-string */
        public string $packageVersion,
        /** @var non-empty-array<string, mixed> */
        public array $config,
    ) {}

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public static function fromData(array $data): ?self
    {
        if (
            !isset($data['phpVersion'], $data['packageVersion'], $data['config'])
            || !\is_string($data['phpVersion']) || '' === $data['phpVersion']
            || !\is_string($data['packageVersion']) || '' === $data['packageVersion']
            || !\is_array($data['config']) || [] === $data['config']
        ) {
            return null;
        }

        return new self(
            phpVersion: $data['phpVersion'],
            packageVersion: $data['packageVersion'],
            // @phpstan-ignore argument.type
            config: $data['config'],
        );
    }

    /**
     * @return array{
     *     phpVersion: non-empty-string,
     *     packageVersion: non-empty-string,
     *     config: non-empty-array<string, mixed>,
     * }
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'phpVersion' => $this->phpVersion,
            'packageVersion' => $this->packageVersion,
            'config' => $this->config,
        ];
    }

    public function equals(self $signature): bool
    {
        return $this->phpVersion === $signature->phpVersion
            && $this->packageVersion === $signature->packageVersion
            && $this->config === $signature->config;
    }
}
