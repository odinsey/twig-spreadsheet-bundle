<?php

namespace MewesK\TwigSpreadsheetBundle\Tests\Twig;

use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class CsvOdsXlsXlsxErrorTwigTest.
 */
class CsvOdsXlsXlsxErrorTwigTest extends BaseTwigTest
{
    /**
     * @return array
     */
    public function formatProvider(): array
    {
        return [['csv'], ['ods'], ['xls'], ['xlsx']];
    }

    //
    // Tests
    //

    /**
     * @param string $format
     *
     * @throws \Exception
     *
     * @dataProvider formatProvider
     */
    public function testCellIndexError($format)
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('setCellIndex(): Argument #1 ($cellIndex) must be of type ?int');

        $this->getDocument('cellIndexError', $format);
    }

    /**
     * @param string $format
     *
     * @throws \Exception
     *
     * @dataProvider formatProvider
     */
    public function testDocumentError($format)
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Node "MewesK\TwigSpreadsheetBundle\Twig\Node\DocumentNode" is not allowed inside of Node "MewesK\TwigSpreadsheetBundle\Twig\Node\SheetNode"');

        $this->getDocument('documentError', $format);
    }

    /**
     * @param string $format
     *
     * @throws \Exception
     *
     * @dataProvider formatProvider
     */
    public function testDocumentErrorTextAfter($format)
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Node "Twig\\Node\\TextNode" is not allowed after Node "MewesK\TwigSpreadsheetBundle\Twig\Node\DocumentNode"');

        $this->getDocument('documentErrorTextAfter', $format);
    }

    /**
     * @param string $format
     *
     * @throws \Exception
     *
     * @dataProvider formatProvider
     */
    public function testDocumentErrorTextBefore($format)
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Node "Twig\\Node\\TextNode" is not allowed before Node "MewesK\TwigSpreadsheetBundle\Twig\Node\DocumentNode"');

        $this->getDocument('documentErrorTextBefore', $format);
    }

    /**
     * @param string $format
     *
     * @throws \Exception
     *
     * @dataProvider formatProvider
     */
    public function testRowIndexError($format)
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('setRowIndex(): Argument #1 ($rowIndex) must be of type ?int');

        $this->getDocument('rowIndexError', $format);
    }

    /**
     * @param string $format
     *
     * @throws \Exception
     *
     * @dataProvider formatProvider
     */
    public function testSheetError($format)
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Node "MewesK\TwigSpreadsheetBundle\Twig\Node\RowNode" is not allowed inside of Node "MewesK\TwigSpreadsheetBundle\Twig\Node\DocumentNode"');

        $this->getDocument('sheetError', $format);
    }
}
