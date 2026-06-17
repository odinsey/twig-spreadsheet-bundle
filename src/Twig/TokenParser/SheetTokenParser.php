<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\TokenParser;

use MewesK\TwigSpreadsheetBundle\Twig\Node\SheetNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;

class SheetTokenParser extends BaseTokenParser
{
    public function configureParameters(Token $token): array
    {
        return [
            'index' => [
                'type' => self::PARAMETER_TYPE_VALUE,
                'default' => new ConstantExpression(null, $token->getLine()),
            ],
            'properties' => [
                'type' => self::PARAMETER_TYPE_ARRAY,
                'default' => new ArrayExpression([], $token->getLine()),
            ],
        ];
    }

    public function createNode(array $nodes = [], int $lineNo = 0): Node
    {
        return new SheetNode($nodes, $this->getAttributes(), $lineNo, $this->getTag());
    }

    public function getTag(): string
    {
        return 'xlssheet';
    }
}