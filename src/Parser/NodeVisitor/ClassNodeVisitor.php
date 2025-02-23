<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser\NodeVisitor;

use DBublik\UnusedClassHunter\Parser\ParsedFile;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class ClassNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly bool $isStrict = false,
        private ParsedFile $parsedFile = new ParsedFile(),
    ) {}

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    #[\Override]
    public function beforeTraverse(array $nodes): array
    {
        $this->parsedFile = new ParsedFile();

        return $nodes;
    }

    #[\Override]
    public function enterNode(Node $node): ?int
    {
        if ($node instanceof ConstFetch) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof ClassLike) {
            $this->readClass($node);
        } elseif ($node instanceof ClassMethod) {
            $this->readClassMethod($node);
        } elseif ($node instanceof Name) {
            $this->readUsedClassName($node);
        }

        return null;
    }

    public function getParsedFile(): ParsedFile
    {
        return $this->parsedFile;
    }

    private function readClass(ClassLike $node): void
    {
        $namespace = $node->namespacedName;

        if (
            !$namespace instanceof Name
            || !$node->name instanceof Identifier
        ) {
            return;
        }

        // @phpstan-ignore argument.type
        $this->parsedFile->setClassName($namespace->toString());

        if (-1 !== $startLine = $node->getStartLine()) {
            $this->parsedFile->setClassStartLine($startLine);
        }

        if (
            (null !== $doc = $node->getDocComment())
            && 1 === preg_match('/@api\b/', $doc->getText())
        ) {
            $this->parsedFile->setHasClassApiTag(true);
        }

        if ($node instanceof Class_) {
            if ($node->extends instanceof Name) {
                // @phpstan-ignore argument.type
                $this->parsedFile->addExtends($node->extends->toString());
            }

            foreach ($node->implements as $implement) {
                // @phpstan-ignore argument.type
                $this->parsedFile->addImplement($implement->toString());
            }
        } elseif ($node instanceof Interface_) {
            foreach ($node->extends as $extend) {
                // @phpstan-ignore argument.type
                $this->parsedFile->addExtends($extend->toString());
            }
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                // @phpstan-ignore argument.type
                $this->parsedFile->addAttribute($attr->name->toString());
            }
        }
    }

    private function readClassMethod(ClassMethod $node): void
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                // @phpstan-ignore argument.type
                $this->parsedFile->addAttribute($attr->name->toString());
            }
        }
    }

    private function readUsedClassName(Name $node): void
    {
        if ($node->isSpecialClassName()) {
            return;
        }

        $parent = $node->getAttribute('parent');

        if (
            $parent instanceof Namespace_
            || $parent instanceof FuncCall
            || $parent instanceof ClassMethod
        ) {
            return;
        }

        if (
            $this->isStrict
            && $parent instanceof UseItem
        ) {
            return;
        }

        // @phpstan-ignore argument.type
        $this->parsedFile->addUsedClass($node->toString());
    }
}
