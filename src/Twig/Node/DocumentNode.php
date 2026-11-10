<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use MewesK\TwigSpreadsheetBundle\Wrapper\PhpSpreadsheetWrapper;
use Twig\Attribute\YieldReady;
use Twig\Compiler;

#[YieldReady]
class DocumentNode extends BaseNode
{
    public function compile(Compiler $compiler): void
    {
        // everything echoed/yielded by the body is discarded, the document itself is written by endDocument()
        $compiler->addDebugInfo($this)
            ->write(self::CODE_INSTANCE.' = new '.PhpSpreadsheetWrapper::class.'($context, $this->env, ')
                ->repr($this->attributes)
            ->raw(');'.PHP_EOL)
            ->write(self::CODE_INSTANCE.'->startDocument(')
                ->subcompile($this->getNode('properties'))
            ->raw(');'.PHP_EOL)
            ;
        $this->compileCapture($compiler, $this->getNode('body'));
        $compiler
            ->addDebugInfo($this)
            ->write(self::CODE_INSTANCE.'->endDocument();'.PHP_EOL)
            ->write('unset('.self::CODE_INSTANCE.');'.PHP_EOL);
    }

    public function getAllowedParents(): array
    {
        return [];
    }
}