<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use MewesK\TwigSpreadsheetBundle\Helper\Filesystem;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use Symfony\Bridge\Twig\AppVariable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DocumentWrapper extends BaseWrapper
{
    protected ?Spreadsheet $object = null;
    protected array $attributes;

    public function __construct(array $context, Environment $environment, array $attributes = [])
    {
        parent::__construct($context, $environment);

        $this->attributes = $attributes;
    }

    /**
     * @throws \RuntimeException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function start(array $properties = []): void
    {
        // load template
        if (isset($properties['template'])) {
            $templatePath = $this->expandPath($properties['template']);
            $reader = IOFactory::createReaderForFile($templatePath);
            $this->object = $reader->load($templatePath);
        } else {
            $this->object = new Spreadsheet();
            $this->object->removeSheetByIndex(0);
        }

        $this->parameters['properties'] = $properties;

        $this->setProperties($properties);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \LogicException
     */
    public function end(): void
    {
        if ($this->object === null) {
            throw new \LogicException();
        }

        $format = null;

        // try document property
        if (isset($this->parameters['format'])) {
            $format = $this->parameters['format'];
        } elseif (isset($this->context['app'])) {
            $appVariable = $this->context['app'];
            if ($appVariable instanceof AppVariable && $appVariable->getRequest() !== null) {
                $format = $appVariable->getRequest()->getRequestFormat();
            }
        }

        // set default
        if ($format === null || !is_string($format)) {
            $format = 'xlsx';
        } else {
            $format = strtolower($format);
        }

        // set up mPDF
        if ($format === 'pdf') {
            if (!class_exists('\Mpdf\Mpdf')) {
                throw new Exception('Error loading mPDF. Is mPDF correctly installed?');
            }
            IOFactory::registerWriter('Pdf', Mpdf::class);
        }

        /** @var BaseWriter $writer */
        $writer = IOFactory::createWriter($this->object, ucfirst($format));
        $writer->setPreCalculateFormulas($this->attributes['preCalculateFormulas'] ?? true);

        // set up XML cache
        if ($this->attributes['cache']['xml'] !== false) {
            Filesystem::mkdir($this->attributes['cache']['xml']);
            $writer->setUseDiskCaching(true, $this->attributes['cache']['xml']);
        }

        $writer->save('php://output');

        $this->object = null;
        $this->parameters = [];
    }

    public function getObject(): ?Spreadsheet
    {
        return $this->object;
    }

    public function setObject(?Spreadsheet $object = null): void
    {
        $this->object = $object;
    }

    protected function configureMappings(): array
    {
        return [
            'category' => function ($value) { $this->object->getProperties()->setCategory($value); },
            'company' => function ($value) { $this->object->getProperties()->setCompany($value); },
            'created' => function ($value) { $this->object->getProperties()->setCreated($value); },
            'creator' => function ($value) { $this->object->getProperties()->setCreator($value); },
            'defaultStyle' => function ($value) { $this->object->getDefaultStyle()->applyFromArray($value); },
            'description' => function ($value) { $this->object->getProperties()->setDescription($value); },
            'format' => function ($value) { $this->parameters['format'] = $value; },
            'keywords' => function ($value) { $this->object->getProperties()->setKeywords($value); },
            'lastModifiedBy' => function ($value) { $this->object->getProperties()->setLastModifiedBy($value); },
            'manager' => function ($value) { $this->object->getProperties()->setManager($value); },
            'modified' => function ($value) { $this->object->getProperties()->setModified($value); },
            'security' => [
                'lockRevision' => function ($value) { $this->object->getSecurity()->setLockRevision($value); },
                'lockStructure' => function ($value) { $this->object->getSecurity()->setLockStructure($value); },
                'lockWindows' => function ($value) { $this->object->getSecurity()->setLockWindows($value); },
                'revisionsPassword' => function ($value) { $this->object->getSecurity()->setRevisionsPassword($value); },
                'workbookPassword' => function ($value) { $this->object->getSecurity()->setWorkbookPassword($value); },
            ],
            'subject' => function ($value) { $this->object->getProperties()->setSubject($value); },
            'template' => function ($value) { $this->parameters['template'] = $value; },
            'title' => function ($value) { $this->object->getProperties()->setTitle($value); },
        ];
    }

    private function expandPath(string $path): string
    {
        $loader = $this->environment->getLoader();

        if ($loader instanceof FilesystemLoader && mb_strpos($path, '@') === 0) {
            foreach ($loader->getNamespaces() as $namespace) {
                if (mb_strpos($path, $namespace) === 1) {
                    foreach ($loader->getPaths($namespace) as $namespacePath) {
                        $expandedPathAttribute = str_replace('@'.$namespace, $namespacePath, $path);
                        if (Filesystem::exists($expandedPathAttribute)) {
                            return $expandedPathAttribute;
                        }
                    }
                }
            }
        }

        return $path;
    }
}
