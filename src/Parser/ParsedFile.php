<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

final class ParsedFile
{
    /** @var list<class-string> */
    private array $usedClasses = [];

    /** @var null|class-string */
    private ?string $className = null;

    /** @var non-negative-int */
    private int $classStartLine = 0;
    private bool $hasClassApiTag = false;

    /** @var list<class-string> */
    private array $extends = [];

    /** @var list<class-string> */
    private array $implements = [];

    /** @var list<class-string> */
    private array $attributes = [];

    /**
     * @return list<class-string>
     */
    public function getUsedClasses(): array
    {
        return array_values(array_unique($this->usedClasses));
    }

    /**
     * @param class-string $usedClass
     */
    public function addUsedClass(string $usedClass): void
    {
        $this->usedClasses[] = $usedClass;
    }

    /**
     * @return null|class-string
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param class-string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return non-negative-int
     */
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

    public function hasClassApiTag(): bool
    {
        return $this->hasClassApiTag;
    }

    public function setHasClassApiTag(bool $hasClassApiTag): void
    {
        $this->hasClassApiTag = $hasClassApiTag;
    }

    /**
     * @return list<class-string>
     */
    public function getExtends(): array
    {
        return $this->extends;
    }

    /**
     * @param class-string $extend
     */
    public function addExtends(string $extend): void
    {
        $this->extends[] = $extend;
    }

    /**
     * @return list<class-string>
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @param class-string $implement
     */
    public function addImplement(string $implement): void
    {
        $this->implements[] = $implement;
    }

    /**
     * @return list<class-string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param class-string $attribute
     */
    public function addAttribute(string $attribute): void
    {
        $this->attributes[] = $attribute;
    }
}
