<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use Twig\Attribute\YieldReady;
use Twig\Compiler;

#[YieldReady]
class RowNode extends BaseNode
{
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->setRowIndex(')
                ->subcompile($this->getNode('index'))
            ->raw(');'.PHP_EOL)
            ->write(self::CODE_INSTANCE.'->startRow('.self::CODE_INSTANCE.'->getRowIndex());'.PHP_EOL)
            ->subcompile($this->getNode('body'))
            ->addDebugInfo($this)
            ->write(self::CODE_INSTANCE.'->endRow();'.PHP_EOL);
    }

    public function getAllowedParents(): array
    {
        return [
            SheetNode::class,
        ];
    }
}