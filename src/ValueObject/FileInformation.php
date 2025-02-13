<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\ValueObject;

final class FileInformation implements \JsonSerializable
{
    private ?string $file = null;

    /** @var string[] */
    private array $usedClassNames = [];
    private ?string $className = null;
    private int $classStartLine = 0;

    /** @var string[] */
    private array $extends = [];

    /** @var string[] */
    private array $implements = [];

    /** @var string[] */
    private array $attributes = [];

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
            $self->file = $data['file'];
            $self->usedClassNames = $data['usedClassNames'];
            $self->className = $data['className'];
            $self->classStartLine = $data['classStartLine'];
            $self->extends = $data['extends'];
            $self->implements = $data['implements'];
            $self->attributes = $data['attributes'];
            // @phpstan-ignore catch.neverThrown
        } catch (\Throwable) {
            return null;
        }

        return $self;
    }

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
     * @return string[]
     */
    public function getUsedClassNames(): array
    {
        return array_unique($this->usedClassNames);
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

    public function setClassStartLine(int $classStartLine): void
    {
        $this->classStartLine = $classStartLine;
    }

    /**
     * @return string[]
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
     * @return string[]
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
     * @return string[]
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
