<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\TokenParser;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

abstract class BaseTokenParser extends AbstractTokenParser
{
    const PARAMETER_TYPE_ARRAY = 0;
    const PARAMETER_TYPE_VALUE = 1;

    private array $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function configureParameters(Token $token): array
    {
        return [];
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    abstract public function createNode(array $nodes = [], int $lineNo = 0): Node;

    public function hasBody(): bool
    {
        return true;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws SyntaxError
     */
    public function parse(Token $token): Node
    {
        // parse parameters
        $nodes = $this->parseParameters($this->configureParameters($token));

        // parse body
        if ($this->hasBody()) {
            $nodes['body'] = $this->parseBody();
        }

        return $this->createNode($nodes, $token->getLine());
    }

    /**
     * @throws \InvalidArgumentException
     * @throws SyntaxError
     */
    private function parseParameters(array $parameterConfiguration = []): array
    {
        // parse expressions
        $expressions = [];
        while (!$this->parser->getStream()->test(Token::BLOCK_END_TYPE)) {
            $expressions[] = $this->parser->getExpressionParser()->parseExpression();
        }

        // end of expressions
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        // map expressions to parameters
        $parameters = [];
        foreach ($parameterConfiguration as $parameterName => $parameterOptions) {
            // try mapping expression
            $expression = reset($expressions);
            if ($expression !== false) {
                switch ($parameterOptions['type']) {
                    case self::PARAMETER_TYPE_ARRAY:
                        $valid = $expression instanceof ArrayExpression;
                        break;
                    case self::PARAMETER_TYPE_VALUE:
                        $valid = !($expression instanceof ArrayExpression);
                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid parameter type');
                }

                if ($valid) {
                    $parameters[$parameterName] = array_shift($expressions);
                    continue;
                }
            }

            if ($parameterOptions['default'] === false) {
                throw new SyntaxError('A required parameter is missing');
            }
            $parameters[$parameterName] = $parameterOptions['default'];
        }

        if (count($expressions) > 0) {
            throw new SyntaxError('Too many parameters');
        }

        return $parameters;
    }

    private function parseBody(): Node
    {
        $body = $this->parser->subparse(function (Token $token) { return $token->test('end'.$this->getTag()); }, true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        return $body;
    }
}