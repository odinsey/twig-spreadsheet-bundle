<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\NodeVisitor;

use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * @deprecated since 1.1.1, no longer registered: the spreadsheet wrapper is not passed as an extra
 *             argument to macro calls any more (Twig 3.29 deprecates unknown named arguments, Twig 4 rejects them).
 *             Macros now get it from PhpSpreadsheetWrapper::fixContext(). Kept as a no-op for BC.
 */
class MacroContextNodeVisitor implements NodeVisitorInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }
}
