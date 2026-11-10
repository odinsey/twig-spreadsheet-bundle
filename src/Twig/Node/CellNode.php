<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use Twig\Attribute\YieldReady;
use Twig\Compiler;

#[YieldReady]
class CellNode extends BaseNode
{
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->setCellIndex(')
                ->subcompile($this->getNode('index'))
            ->raw(');'.PHP_EOL)
            ;
        $this->compileCapture($compiler, $this->getNode('body'), '$cellValue');
        $compiler
            ->write('$cellValue = trim($cellValue);'.PHP_EOL)
            ->write(self::CODE_INSTANCE.'->startCell($cellValue, ')
                ->subcompile($this->getNode('properties'))
            ->raw(');'.PHP_EOL)
            ->write(self::CODE_INSTANCE.'->endCell();'.PHP_EOL)
            ->write('unset($cellValue);'.PHP_EOL);
    }

    public function getAllowedParents(): array
    {
        return [
            RowNode::class,
        ];
    }
}