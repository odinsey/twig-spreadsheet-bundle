<?php

namespace MewesK\TwigSpreadsheetBundle\Tests\Twig;

use MewesK\TwigSpreadsheetBundle\Twig\NodeVisitor\MacroContextNodeVisitor;
use MewesK\TwigSpreadsheetBundle\Twig\NodeVisitor\SyntaxCheckNodeVisitor;
use MewesK\TwigSpreadsheetBundle\Twig\TwigSpreadsheetExtension;
use MewesK\TwigSpreadsheetBundle\Wrapper\PhpSpreadsheetWrapper;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\Source;

/**
 * Covers SyntaxCheckNodeVisitor and MacroContextNodeVisitor at compile time and guards
 * against the Twig deprecations (AbstractNodeVisitor, ExpressionParser) coming back.
 */
class NodeVisitorTest extends TestCase
{
    private const MACRO_TEMPLATE = <<<'TWIG'
{% macro cell(value) %}{% xlscell %}{{ value }}{% endxlscell %}{% endmacro %}
{% import _self as macros %}
{% xlsdocument %}
    {% block sheet %}
        {% xlssheet 'Test' %}
            {% block row %}
                {% xlsrow %}{{ macros.cell('Hello') }}{% endxlsrow %}
            {% endblock %}
        {% endxlssheet %}
    {% endblock %}
{% endxlsdocument %}
TWIG;

    private function createEnvironment(): Environment
    {
        $environment = new Environment(new ArrayLoader(['macro_blocks.twig' => self::MACRO_TEMPLATE]), ['debug' => true, 'strict_variables' => true, 'cache' => false]);
        $environment->addExtension(new TwigSpreadsheetExtension());

        return $environment;
    }

    /**
     * @return string[] deprecation messages raised while running $callback
     */
    private function collectDeprecations(callable $callback): array
    {
        $messages = [];
        set_error_handler(static function (int $level, string $message) use (&$messages): bool {
            $messages[] = $message;

            return true;
        }, E_USER_DEPRECATED | E_DEPRECATED);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $messages;
    }

    public function testVisitorsImplementInterfaceDirectly(): void
    {
        foreach ([new MacroContextNodeVisitor(), new SyntaxCheckNodeVisitor()] as $visitor) {
            static::assertInstanceOf(NodeVisitorInterface::class, $visitor);
            static::assertNotInstanceOf(AbstractNodeVisitor::class, $visitor);
            static::assertSame(0, $visitor->getPriority());
        }
    }

    public function testCompilingMacrosAndBlocksPassesBothVisitors(): void
    {
        $environment = $this->createEnvironment();

        $deprecations = $this->collectDeprecations(static function () use ($environment): void {
            $environment->compileSource(new Source(self::MACRO_TEMPLATE, 'macro_blocks.twig'));
        });

        foreach ($deprecations as $message) {
            static::assertStringNotContainsString('ExpressionParser', $message);
            static::assertStringNotContainsString('AbstractNodeVisitor', $message);
        }
    }

    public function testMacroCallsDoNotReceiveTheWrapperAsArgument(): void
    {
        $compiled = $this->createEnvironment()->compileSource(new Source(self::MACRO_TEMPLATE, 'macro_blocks.twig'));

        // passing an unknown "_tsb" argument is deprecated since Twig 3.29 and rejected by Twig 4
        static::assertDoesNotMatchRegularExpression('/[\'"]'.PhpSpreadsheetWrapper::INSTANCE_KEY.'[\'"]\s*=>/', $compiled);
    }

    public function testMacroFindsTheActiveWrapperWithoutDeprecations(): void
    {
        $cache = sys_get_temp_dir().'/tsb_macro_test';
        $environment = new Environment(new ArrayLoader(['macro_blocks.twig' => self::MACRO_TEMPLATE]), ['debug' => true, 'strict_variables' => true, 'cache' => false]);
        $environment->addExtension(new TwigSpreadsheetExtension(['cache' => ['bitmap' => $cache.'/bitmap', 'xml' => false]]));
        $template = $environment->load('macro_blocks.twig');

        $deprecations = $this->collectDeprecations(static function () use ($template): void {
            ob_start();
            try {
                $template->render([]);
            } finally {
                ob_end_clean();
            }
        });

        static::assertSame([], array_values(array_filter($deprecations, static fn (string $m): bool => str_contains($m, '_tsb'))));
    }

    public function testSyntaxCheckRejectsNodeInsideForbiddenParent(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('is not allowed inside of Node');

        $this->createEnvironment()->compileSource(new Source(
            "{% xlsdocument %}{% xlssheet 'a' %}{% xlsdocument %}{% endxlsdocument %}{% endxlssheet %}{% endxlsdocument %}",
            'invalid.twig'
        ));
    }

    public function testSyntaxCheckStateIsResetAfterError(): void
    {
        $environment = $this->createEnvironment();

        try {
            $environment->compileSource(new Source(
                "{% xlsdocument %}{% xlssheet 'a' %}{% xlsdocument %}{% endxlsdocument %}{% endxlssheet %}{% endxlsdocument %}",
                'invalid.twig'
            ));
            static::fail('SyntaxError expected');
        } catch (SyntaxError $e) {
            // expected
        }

        // a valid template must compile afterwards with the same environment
        static::assertNotEmpty($environment->compileSource(new Source(self::MACRO_TEMPLATE, 'macro_blocks.twig')));
    }
}
