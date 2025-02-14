<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use DBublik\UnusedClassHunter\Parser\NodeVisitor\ClassNodeVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;

final class ClassNodeTraverser extends NodeTraverser
{
    public function __construct(
        public readonly ClassNodeVisitor $visitor = new ClassNodeVisitor(),
    ) {
        parent::__construct(
            new NameResolver(),
            new NodeConnectingVisitor(),
            $this->visitor,
        );
    }
}
