<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use MewesK\TwigSpreadsheetBundle\Wrapper\HeaderFooterWrapper;
use Twig\Compiler;

class AlignmentNode extends BaseNode
{
    private string $alignment;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $nodes = [], array $attributes = [], int $lineNo = 0, ?string $tag = null, string $alignment = HeaderFooterWrapper::ALIGNMENT_CENTER)
    {
        parent::__construct($nodes, $attributes, $lineNo, $tag);

        $this->alignment = HeaderFooterWrapper::validateAlignment(strtolower($alignment));
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->startAlignment(')
                ->repr($this->alignment)
            ->raw(');'.PHP_EOL)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write('$alignmentValue = trim(ob_get_clean());'.PHP_EOL)
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