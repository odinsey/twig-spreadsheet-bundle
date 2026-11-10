<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use MewesK\TwigSpreadsheetBundle\Wrapper\HeaderFooterWrapper;
use Twig\Attribute\YieldReady;
use Twig\Compiler;

#[YieldReady]
class AlignmentNode extends BaseNode
{
    private string $alignment;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $nodes = [], array $attributes = [], int $lineNo = 0, string $alignment = HeaderFooterWrapper::ALIGNMENT_CENTER)
    {
        parent::__construct($nodes, $attributes, $lineNo);

        $this->alignment = HeaderFooterWrapper::validateAlignment(strtolower($alignment));
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->startAlignment(')
                ->repr($this->alignment)
            ->raw(');'.PHP_EOL)
            ;
        $this->compileCapture($compiler, $this->getNode('body'), '$alignmentValue');
        $compiler
            ->write('$alignmentValue = trim($alignmentValue);'.PHP_EOL)
            ->write(self::CODE_INSTANCE.'->endAlignment($alignmentValue);'.PHP_EOL)
            ->write('unset($alignmentValue);'.PHP_EOL);
    }

    public function getAllowedParents(): array
    {
        return [
            HeaderFooterNode::class,
        ];
    }
}