<?php

declare(strict_types=1);

namespace DBublik\UnusedClassHunter\Parser;

use DBublik\UnusedClassHunter\ValueObject\FileInformation;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final readonly class ClassParser
{
    private Parser $parser;

    public function __construct(
        private ClassNodeTraverser $traverser = new ClassNodeTraverser(),
    ) {
        $this->parser = (new ParserFactory())->createForHostVersion();
    }

    public function parse(string $code): FileInformation
    {
        $nodes = $this->parser->parse($code);

        return $this->traverser->traverse($nodes);
    }
}
