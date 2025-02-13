<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;

final readonly class ClassNodeTraverser
{
    public function __construct(
        private NodeTraverser $traverser = new NodeTraverser(),
        private ClassNodeVisitor $visitor = new ClassNodeVisitor(),
    ) {
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor(new NodeConnectingVisitor());
        $this->traverser->addVisitor($this->visitor);
    }

    /**
     * @param Stmt[] $nodes
     */
    public function traverse(array $nodes): FileInformation
    {
        $this->traverser->traverse($nodes);

        return $this->visitor->getInformation();
    }
}
