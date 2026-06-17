<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use Twig\Environment;

class PhpSpreadsheetWrapper
{
    const INSTANCE_KEY = '_tsb';

    private DocumentWrapper $documentWrapper;
    private SheetWrapper $sheetWrapper;
    private RowWrapper $rowWrapper;
    private CellWrapper $cellWrapper;
    private HeaderFooterWrapper $headerFooterWrapper;
    private DrawingWrapper $drawingWrapper;

    private ?int $cellIndex = null;
    private ?int $rowIndex = null;

    public function __construct(array $context, Environment $environment, array $attributes = [])
    {
        $this->documentWrapper = new DocumentWrapper($context, $environment, $attributes);
        $this->sheetWrapper = new SheetWrapper($context, $environment, $this->documentWrapper);
        $this->rowWrapper = new RowWrapper($context, $environment, $this->sheetWrapper);
        $this->cellWrapper = new CellWrapper($context, $environment, $this->sheetWrapper);
        $this->headerFooterWrapper = new HeaderFooterWrapper($context, $environment, $this->sheetWrapper);
        $this->drawingWrapper = new DrawingWrapper($context, $environment, $this->sheetWrapper, $this->headerFooterWrapper, $attributes);
    }

    public static function fixContext(array $context): array
    {
        if (!isset($context[self::INSTANCE_KEY]) && isset($context['varargs']) && is_array($context['varargs'])) {
            $args = $context['varargs'];
            foreach ($args as $arg) {
                if ($arg instanceof self) {
                    $context[self::INSTANCE_KEY] = $arg;
                }
            }
        }

        return $context;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \RuntimeException
     */
    public function startDocument(array $properties = []): void
    {
        $this->documentWrapper->start($properties);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function endDocument(): void
    {
        $this->documentWrapper->end();
    }

    /**
     * @param int|string|null $index
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \RuntimeException
     */
    public function startSheet(mixed $index = null, array $properties = []): void
    {
        $this->sheetWrapper->start($index, $properties);
    }

    public function endSheet(): void
    {
        $this->sheetWrapper->end();
    }

    public function startRow(): void
    {
        $this->rowWrapper->start($this->rowIndex);
    }

    public function endRow(): void
    {
        $this->rowWrapper->end();
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \RuntimeException
     */
    public function startCell(mixed $value = null, array $properties = []): void
    {
        $this->cellWrapper->start($this->cellIndex, $value, $properties);
    }

    public function endCell(): void
    {
        $this->cellWrapper->end();
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function startHeaderFooter(string $baseType, ?string $type = null, array $properties = []): void
    {
        $this->headerFooterWrapper->start($baseType, $type, $properties);
    }

    public function endHeaderFooter(): void
    {
        $this->headerFooterWrapper->end();
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function startAlignment(?string $type = null, array $properties = []): void
    {
        $this->headerFooterWrapper->startAlignment($type, $properties);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function endAlignment(?string $value = null): void
    {
        $this->headerFooterWrapper->endAlignment($value);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function startDrawing(string $path, array $properties = []): void
    {
        $this->drawingWrapper->start($path, $properties);
    }

    public function endDrawing(): void
    {
        $this->drawingWrapper->end();
    }

    public function getCellIndex(): ?int
    {
        return $this->cellIndex;
    }

    public function setCellIndex(?int $cellIndex = null): void
    {
        $this->cellIndex = $cellIndex;
    }

    public function getRowIndex(): ?int
    {
        return $this->rowIndex;
    }

    public function setRowIndex(?int $rowIndex = null): void
    {
        $this->rowIndex = $rowIndex;
    }
}
