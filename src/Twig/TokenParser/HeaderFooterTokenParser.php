<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\TokenParser;

use MewesK\TwigSpreadsheetBundle\Twig\Node\HeaderFooterNode;
use MewesK\TwigSpreadsheetBundle\Wrapper\HeaderFooterWrapper;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;

class HeaderFooterTokenParser extends BaseTokenParser
{
    private string $baseType;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $attributes = [], string $baseType = HeaderFooterWrapper::BASETYPE_HEADER)
    {
        parent::__construct($attributes);

        $this->baseType = HeaderFooterWrapper::validateBaseType(strtolower($baseType));
    }

    public function configureParameters(Token $token): array
    {
        return [
            'type' => [
                'type' => self::PARAMETER_TYPE_VALUE,
                'default' => new ConstantExpression(null, $token->getLine()),
            ],
            'properties' => [
                'type' => self::PARAMETER_TYPE_ARRAY,
                'default' => new ArrayExpression([], $token->getLine()),
            ],
        ];
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function createNode(array $nodes = [], int $lineNo = 0): Node
    {
        return new HeaderFooterNode($nodes, $this->getAttributes(), $lineNo, $this->getTag(), $this->baseType);
    }

    public function getTag(): string
    {
        return 'xls'.$this->baseType;
    }
}
