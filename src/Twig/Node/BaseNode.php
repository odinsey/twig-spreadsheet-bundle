<?php

namespace MewesK\TwigSpreadsheetBundle\Twig\Node;

use MewesK\TwigSpreadsheetBundle\Wrapper\PhpSpreadsheetWrapper;
use Twig\Compiler;
use Twig\Node\CaptureNode;
use Twig\Node\Node;

abstract class BaseNode extends Node
{
    const CODE_FIX_CONTEXT = '$context = '.PhpSpreadsheetWrapper::class.'::fixContext($context);'.PHP_EOL;

    const CODE_INSTANCE = '$context[\''.PhpSpreadsheetWrapper::INSTANCE_KEY.'\']';

    /**
     * @return string[]
     */
    abstract public function getAllowedParents(): array;

    /**
     * Compiles the given node so that its output (echo or yield, depending on the Twig "use_yield" option)
     * is captured into a string instead of being sent to the output. Emits a complete statement.
     *
     * Equivalent of the former ob_start()/ob_get_clean() pair, which does not work with "yield".
     */
    protected function compileCapture(Compiler $compiler, Node $body, ?string $assignTo = null): void
    {
        $capture = new CaptureNode($body, $body->getTemplateLine());
        $capture->setAttribute('raw', true);

        $compiler->write($assignTo !== null ? $assignTo.' = ' : '')->subcompile($capture)->raw(PHP_EOL);
    }
}
