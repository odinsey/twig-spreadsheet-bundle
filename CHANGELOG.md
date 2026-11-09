## 1.1.0

 * Compatibility with recent Twig 3.x: removed the deprecations raised on every template compilation.
   * `MacroContextNodeVisitor` and `SyntaxCheckNodeVisitor` implement `Twig\NodeVisitor\NodeVisitorInterface` directly instead of extending the deprecated `AbstractNodeVisitor` (Twig 3.9).
   * `BaseTokenParser` uses `Parser::parseExpression()` (Twig 3.21+) and falls back to `getExpressionParser()->parseExpression()` on older Twig.
   * `MacroContextNodeVisitor` uses `ContextVariable` instead of the deprecated `NameExpression` (Twig 3.15+), with a fallback on older Twig.
 * Fixed macros: since Twig 3.15 macro calls are `MacroReferenceExpression` nodes instead of `MethodCallExpression`, so the spreadsheet wrapper was no longer passed to macros (`Undefined array key "_tsb"`).
 * `TemplateWrapper::unwrap()` is not called by this bundle. The Twig 3.30 notice "Calling TemplateWrapper::unwrap() without arguments" comes from `symfony/twig-bridge` (`TwigRendererEngine`); it disappears with a Symfony release that passes the environment.
 * **Minimum requirements raised**: PHP >= 8.1, Symfony ^5.4 || ^6.4 || ^7.0, Twig ^3.12 (the test suite fails on Twig 3.9 and 3.10, which render empty documents with this bundle).
 * Test suite ported to PHPUnit 9 / namespaced Twig classes; the legacy `tests/Functional` suite (Symfony 3 kernel, SensioFrameworkExtraBundle) is not part of the default test run any more.
 * Added `NodeVisitorTest`.
 * Not addressed (still deprecated in Twig, no change in behaviour): the `tag` constructor argument of the nodes (3.12) and `#[YieldReady]` (3.9).

## 1.x

 * Removed xlsblock, xlsinclude and xlsmacro. The native Twig functions do work now like they are supposed to do!
 * Removed explicitValue cell property. Use dataType instead. However PhpSpread is handling leading zeros properly now so you probably don't have to use this property anyway.
 * Changed header/footer types. Just use 'even', 'odd', 'first' now.
 * ["Column indexes are now based on 1. So column A is the index 1. This is consistent with rows starting at 1 and Excel function COLUMN() that returns 1 for column A."](https://phpspreadsheet.readthedocs.io/en/develop/topics/migration-from-PHPExcel/#column-index-based-on-1)
