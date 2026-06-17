<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use Twig\Compiler;

class SheetNode extends BaseNode
{
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->startSheet(')
                ->subcompile($this->getNode('index'))->raw(', ')
                ->subcompile($this->getNode('properties'))
            ->raw(');'.PHP_EOL)
            ->subcompile($this->getNode('body'))
            ->addDebugInfo($this)
            ->write(self::CODE_INSTANCE.'->endSheet();'.PHP_EOL);
    }

    public function getAllowedParents(): array
    {
        return [
            DocumentNode::class,
        ];
    }
}