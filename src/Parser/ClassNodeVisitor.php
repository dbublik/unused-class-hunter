<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use DBublik\UnusedClassHunter\ValueObject\FileInformation;
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
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class ClassNodeVisitor extends NodeVisitorAbstract
{
    private FileInformation $information;

    public function __construct()
    {
        $this->information = new FileInformation();
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    public function beforeTraverse(array $nodes): array
    {
        $this->information = new FileInformation();

        return $nodes;
    }

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

    public function getInformation(): FileInformation
    {
        return $this->information;
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

        $this->information->setClassName($namespace->toString());
        $this->information->setClassStartLine((int) $node->getAttribute('startLine'));

        if ($node instanceof Class_) {
            if ($node->extends instanceof Name) {
                $this->information->addExtends($node->extends->toString());
            }

            foreach ($node->implements as $implement) {
                $this->information->addImplement($implement->toString());
            }
        } elseif ($node instanceof Interface_) {
            foreach ($node->extends as $extend) {
                $this->information->addExtends($extend->toString());
            }
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $this->information->addAttribute($attr->name->toString());
            }
        }
    }

    private function readClassMethod(ClassMethod $node): void
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $this->information->addAttribute($attr->name->toString());
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

        $this->information->addUsedClassName($node->toString());
    }
}
