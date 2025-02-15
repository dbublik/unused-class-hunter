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
    ) {}

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public static function fromData(array $data): ?self
    {
        if (
            !isset($data['phpVersion'], $data['packageVersion'])
            || !\is_string($data['phpVersion']) || '' === $data['phpVersion']
            || !\is_string($data['packageVersion']) || '' === $data['packageVersion']
        ) {
            return null;
        }

        return new self(
            phpVersion: $data['phpVersion'],
            packageVersion: $data['packageVersion'],
        );
    }

    /**
     * @return array{
     *     phpVersion: non-empty-string,
     *     packageVersion: non-empty-string,
     * }
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'phpVersion' => $this->phpVersion,
            'packageVersion' => $this->packageVersion,
        ];
    }

    public function equals(self $signature): bool
    {
        return $this->phpVersion === $signature->phpVersion
            && $this->packageVersion === $signature->packageVersion;
    }
}
