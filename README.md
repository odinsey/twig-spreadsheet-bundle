# TwigSpreadsheetBundle

This Symfony bundle integrates PhpSpreadsheet into Symfony using Twig.

## Features

 * Easy to use Twig integration including ``macro`` and ``include`` support
 * Use existing spreadsheet files as templates. The easiest way to customize fonts, colors, etc.

## Supported output formats

The supported output formats are directly based on the capabilities of PhpSpreadsheet.

 * Open Document Format/OASIS (.ods)
 * Office Open XML (.xlsx) Excel 2007 and above
 * BIFF 8 (.xls) Excel 97 and above
 * CSV
 * PDF (using mPDF, which needs to be installed separately)

## Requirements

**PHP & Symfony:**

 * PHP 8.0 or newer
 * Symfony 5.4, 6.x or 7.x
 * Twig 3.x

**PhpSpreadsheet:**

 * phpoffice/phpspreadsheet ^1.29 or ^2.0
 * PHP extension php_zip enabled
 * PHP extension php_xml enabled
 * PHP extension php_gd2 enabled (optional, required for exact column width autocalculation)

## Installation

```bash
composer require odinsey/twig-spreadsheet-bundle
```

## Version history

| Version | PHP | Symfony | Twig |
|---------|-----|---------|------|
| 1.x     | 8.0+ | 5.4 / 6 / 7 | 3.x |
| 0.9.x   | 7.0+ | 3.2 / 4 / 5 | 2.x |

## License

This bundle is under the MIT license. See the complete license in the bundle:

[LICENSE](https://github.com/odinsey/twig-spreadsheet-bundle/blob/master/LICENSE)
