<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\NodeVisitor;

use MewesK\TwigSpreadsheetBundle\Wrapper\PhpSpreadsheetWrapper;
use Twig\Environment;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\MacroReferenceExpression;
use Twig\Node\Expression\MethodCallExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Expression\Variable\ContextVariable;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

class MacroContextNodeVisitor implements NodeVisitorInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        // add wrapper instance as argument on all macro calls
        // (macro calls are MethodCallExpression before Twig 3.15 and MacroReferenceExpression since then)
        if ($node instanceof MethodCallExpression || $node instanceof MacroReferenceExpression) {
            $keyNode = new ConstantExpression(PhpSpreadsheetWrapper::INSTANCE_KEY, $node->getTemplateLine());

            // add wrapper even if it not exists, we fix that later
            // NameExpression is deprecated since Twig 3.15 in favor of ContextVariable
            $valueNode = class_exists(ContextVariable::class)
                ? new ContextVariable(PhpSpreadsheetWrapper::INSTANCE_KEY, $node->getTemplateLine())
                : new NameExpression(PhpSpreadsheetWrapper::INSTANCE_KEY, $node->getTemplateLine());
            $valueNode->setAttribute('ignore_strict_check', true);

            /** @var ArrayExpression $argumentsNode */
            $argumentsNode = $node->getNode('arguments');
            $argumentsNode->addElement($valueNode, $keyNode);
        }

        return $node;
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }
}