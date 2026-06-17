<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use MewesK\TwigSpreadsheetBundle\Helper\Filesystem;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing;
use Twig\Environment;

class DrawingWrapper extends BaseWrapper
{
    protected SheetWrapper $sheetWrapper;
    protected HeaderFooterWrapper $headerFooterWrapper;
    protected Drawing|HeaderFooterDrawing|null $object = null;
    protected array $attributes;

    public function __construct(array $context, Environment $environment, SheetWrapper $sheetWrapper, HeaderFooterWrapper $headerFooterWrapper, array $attributes = [])
    {
        parent::__construct($context, $environment);

        $this->sheetWrapper = $sheetWrapper;
        $this->headerFooterWrapper = $headerFooterWrapper;
        $this->attributes = $attributes;
    }

    /**
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function start(string $path, array $properties = []): void
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }

        // create local copy of the asset
        $tempPath = $this->createTempCopy($path);

        // add to header/footer
        if ($this->headerFooterWrapper->getObject()) {
            $headerFooterParameters = $this->headerFooterWrapper->getParameters();
            $alignment = $this->headerFooterWrapper->getAlignmentParameters()['type'];
            $location = '';

            switch ($alignment) {
                case HeaderFooterWrapper::ALIGNMENT_CENTER:
                    $location .= 'C';
                    $headerFooterParameters['value'][HeaderFooterWrapper::ALIGNMENT_CENTER] .= '&G';
                    break;
                case HeaderFooterWrapper::ALIGNMENT_LEFT:
                    $location .= 'L';
                    $headerFooterParameters['value'][HeaderFooterWrapper::ALIGNMENT_LEFT] .= '&G';
                    break;
                case HeaderFooterWrapper::ALIGNMENT_RIGHT:
                    $location .= 'R';
                    $headerFooterParameters['value'][HeaderFooterWrapper::ALIGNMENT_RIGHT] .= '&G';
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown alignment type "%s"', $alignment));
            }

            $location .= $headerFooterParameters['baseType'] === HeaderFooterWrapper::BASETYPE_HEADER ? 'H' : 'F';

            $this->object = new HeaderFooterDrawing();
            $this->object->setPath($tempPath);
            $this->headerFooterWrapper->getObject()->addImage($this->object, $location);
            $this->headerFooterWrapper->setParameters($headerFooterParameters);
        } else {
            $this->object = new Drawing();
            $this->object->setWorksheet($this->sheetWrapper->getObject());
            $this->object->setPath($tempPath);
        }

        $this->setProperties($properties);
    }

    public function end(): void
    {
        $this->object = null;
        $this->parameters = [];
    }

    public function getObject(): Drawing|HeaderFooterDrawing|null
    {
        return $this->object;
    }

    public function setObject(Drawing $object): void
    {
        $this->object = $object;
    }

    protected function configureMappings(): array
    {
        return [
            'coordinates' => function ($value) { $this->object->setCoordinates($value); },
            'description' => function ($value) { $this->object->setDescription($value); },
            'height' => function ($value) { $this->object->setHeight($value); },
            'name' => function ($value) { $this->object->setName($value); },
            'offsetX' => function ($value) { $this->object->setOffsetX($value); },
            'offsetY' => function ($value) { $this->object->setOffsetY($value); },
            'resizeProportional' => function ($value) { $this->object->setResizeProportional($value); },
            'rotation' => function ($value) { $this->object->setRotation($value); },
            'shadow' => [
                'alignment' => function ($value) { $this->object->getShadow()->setAlignment($value); },
                'alpha' => function ($value) { $this->object->getShadow()->setAlpha($value); },
                'blurRadius' => function ($value) { $this->object->getShadow()->setBlurRadius($value); },
                'color' => function ($value) { $this->object->getShadow()->getColor()->setRGB($value); },
                'direction' => function ($value) { $this->object->getShadow()->setDirection($value); },
                'distance' => function ($value) { $this->object->getShadow()->setDistance($value); },
                'visible' => function ($value) { $this->object->getShadow()->setVisible($value); },
            ],
            'width' => function ($value) { $this->object->setWidth($value); },
        ];
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    private function createTempCopy(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $tempPath = sprintf('%s/tsb_%s%s', $this->attributes['cache']['bitmap'], md5($path), $extension ? '.'.$extension : '');

        if (!Filesystem::exists($tempPath)) {
            $data = file_get_contents($path);
            if ($data === false) {
                throw new \InvalidArgumentException($path.' does not exist.');
            }
            Filesystem::dumpFile($tempPath, $data);
            unset($data);
        }

        return $tempPath;
    }
}
