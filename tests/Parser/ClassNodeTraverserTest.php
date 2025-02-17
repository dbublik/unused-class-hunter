<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Tests\Parser;

use DBublik\UnusedClassHunter\Parser\ClassNodeTraverser;
use DBublik\UnusedClassHunter\Parser\NodeVisitor\ClassNodeVisitor;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ClassNodeTraverser::class)]
final class ClassNodeTraverserTest extends TestCase
{
    public function testCreate(): void
    {
        $traverser = new ClassNodeTraverser();

        /** @var list<NodeVisitor> $visitors */
        $visitors = (new \ReflectionProperty(ClassNodeTraverser::class, 'visitors'))->getValue($traverser);
        self::assertCount(3, $visitors);
        self::assertSame(
            [
                NameResolver::class,
                NodeConnectingVisitor::class,
                ClassNodeVisitor::class,
            ],
            array_map(
                static fn (NodeVisitor $visitor): string => $visitor::class,
                $visitors
            )
        );
    }
}
