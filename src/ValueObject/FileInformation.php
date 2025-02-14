<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

final class FileInformation implements \JsonSerializable
{
    private ?string $file = null;

    /** @var list<string> */
    private array $usedClassNames = [];
    private ?string $className = null;

    /** @var non-negative-int */
    private int $classStartLine = 0;

    /** @var list<string> */
    private array $extends = [];

    /** @var list<string> */
    private array $implements = [];

    /** @var list<string> */
    private array $attributes = [];

    /**
     * @todo check all types and subtypes of the array.
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public static function fromData(array $data): ?self
    {
        if (
            !\array_key_exists('className', $data)
            || !isset(
                $data['file'],
                $data['usedClassNames'],
                $data['classStartLine'],
                $data['extends'],
                $data['implements'],
                $data['attributes']
            )
        ) {
            return null;
        }

        $self = new self();

        try {
            // @phpstan-ignore assign.propertyType
            $self->file = $data['file'];
            // @phpstan-ignore assign.propertyType
            $self->usedClassNames = $data['usedClassNames'];
            // @phpstan-ignore assign.propertyType
            $self->className = $data['className'];
            // @phpstan-ignore assign.propertyType
            $self->classStartLine = $data['classStartLine'];
            // @phpstan-ignore assign.propertyType
            $self->extends = $data['extends'];
            // @phpstan-ignore assign.propertyType
            $self->implements = $data['implements'];
            // @phpstan-ignore assign.propertyType
            $self->attributes = $data['attributes'];
            // @phpstan-ignore catch.neverThrown
        } catch (\Throwable) {
            return null;
        }

        return $self;
    }

    /**
     * @return array{
     *     file: ?string,
     *     usedClassNames: list<string>,
     *     className: ?string,
     *     classStartLine: non-negative-int,
     *     extends: list<string>,
     *     implements: list<string>,
     *     attributes: list<string>
     * }
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'file' => $this->file,
            'usedClassNames' => $this->usedClassNames,
            'className' => $this->className,
            'classStartLine' => $this->classStartLine,
            'extends' => $this->extends,
            'implements' => $this->implements,
            'attributes' => $this->attributes,
        ];
    }

    public function getFile(): string
    {
        if (null === $this->file) {
            throw new \LogicException('Something broken');
        }

        return $this->file;
    }

    public function getRelativeFile(): string
    {
        return str_replace(getcwd() . \DIRECTORY_SEPARATOR, '', $this->getFile());
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @return list<string>
     */
    public function getUsedClassNames(): array
    {
        return array_values(array_unique($this->usedClassNames));
    }

    public function addUsedClassName(string $usedClassName): void
    {
        $this->usedClassNames[] = $usedClassName;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getClassStartLine(): int
    {
        return $this->classStartLine;
    }

    /**
     * @param non-negative-int $classStartLine
     */
    public function setClassStartLine(int $classStartLine): void
    {
        $this->classStartLine = $classStartLine;
    }

    /**
     * @return list<string>
     */
    public function getExtends(): array
    {
        return $this->extends;
    }

    public function addExtends(string $extend): void
    {
        $this->extends[] = $extend;
    }

    /**
     * @return list<string>
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    public function addImplement(string $implement): void
    {
        $this->implements[] = $implement;
    }

    /**
     * @return list<string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $attribute): void
    {
        $this->attributes[] = $attribute;
    }
}
