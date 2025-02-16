<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

use DBublik\UnusedClassHunter\Utils\Assert;

final readonly class ClassNode extends AbstractFileNode
{
    /**
     * @param non-empty-string $file
     * @param list<class-string> $usedClasses
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $file,
        array $usedClasses,
        /** @var class-string */
        private string $name,
        /** @var non-negative-int */
        private int $startLine,
        private bool $hasApiTag,
        /** @var list<class-string> */
        private array $extends,
        /** @var list<class-string> */
        private array $implements,
        /** @var list<class-string> */
        private array $attributes,
    ) {
        parent::__construct($file, $usedClasses);

        Assert::listOfString($extends);
        Assert::listOfString($implements);
        Assert::listOfString($attributes);
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public static function fromData(array $data): ?self
    {
        $class = $data['class'] ?? [];

        if (
            !\is_array($class)
            || !isset(
                $data['file'],
                $data['usedClasses'],
                $class['name'],
                $class['startLine'],
                $class['hasApiTag'],
                $class['extends'],
                $class['implements'],
                $class['attributes']
            )
            || !\is_string($data['file']) || '' === $data['file']
            || !\is_array($data['usedClasses'])
            || !\is_string($class['name'])
            || !\is_int($class['startLine']) || $class['startLine'] < 0
            || !\is_bool($class['hasApiTag'])
            || !\is_array($class['extends'])
            || !\is_array($class['implements'])
            || !\is_array($class['attributes'])
        ) {
            return null;
        }

        try {
            return new self(
                file: $data['file'],
                // @phpstan-ignore argument.type
                usedClasses: $data['usedClasses'],
                // @phpstan-ignore argument.type
                name: $class['name'],
                startLine: $class['startLine'],
                hasApiTag: $class['hasApiTag'],
                // @phpstan-ignore argument.type
                extends: $class['extends'],
                // @phpstan-ignore argument.type
                implements: $class['implements'],
                // @phpstan-ignore argument.type
                attributes: $class['attributes'],
            );
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @return array{
     *     file: non-empty-string,
     *     usedClasses: list<class-string>,
     *     class: array{
     *          name: class-string,
     *          startLine: non-negative-int,
     *          hasApiTag: bool,
     *          extends: list<class-string>,
     *          implements: list<class-string>,
     *          attributes: list<class-string>
     *     }
     * }
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'file' => $this->getFile(),
            'usedClasses' => $this->getUsedClasses(),
            'class' => [
                'name' => $this->name,
                'startLine' => $this->startLine,
                'hasApiTag' => $this->hasApiTag,
                'extends' => $this->extends,
                'implements' => $this->implements,
                'attributes' => $this->attributes,
            ],
        ];
    }

    /**
     * @return class-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-negative-int
     */
    public function getStartLine(): int
    {
        return $this->startLine;
    }

    public function hasApiTag(): bool
    {
        return $this->hasApiTag;
    }

    /**
     * @return list<class-string>
     */
    public function getExtends(): array
    {
        return $this->extends;
    }

    /**
     * @return list<class-string>
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @return list<class-string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
