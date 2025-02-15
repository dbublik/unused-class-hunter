<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

final readonly class FileNode extends AbstractFileNode
{
    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public static function fromData(array $data): ?self
    {
        if (
            !isset($data['file'], $data['usedClasses'])
            || !\is_string($data['file']) || '' === $data['file']
            || !\is_array($data['usedClasses'])
        ) {
            return null;
        }

        try {
            return new self(
                file: $data['file'],
                // @phpstan-ignore argument.type
                usedClasses: $data['usedClasses'],
            );
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @return array{
     *     file: non-empty-string,
     *     usedClasses: list<class-string>,
     * }
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'file' => $this->getFile(),
            'usedClasses' => $this->getUsedClasses(),
        ];
    }
}
