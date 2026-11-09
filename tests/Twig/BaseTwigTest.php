<?php

namespace MewesK\TwigSpreadsheetBundle\Tests\Twig;

use MewesK\TwigSpreadsheetBundle\DependencyInjection\Configuration;
use MewesK\TwigSpreadsheetBundle\Helper\Filesystem;
use MewesK\TwigSpreadsheetBundle\Twig\TwigSpreadsheetExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BaseTwigTest.
 */
abstract class BaseTwigTest extends TestCase
{
    const CACHE_PATH = './../../var/cache';
    const RESULT_PATH = './../../var/result';
    const RESOURCE_PATH = './Fixtures/views';
    const TEMPLATE_PATH = './Fixtures/templates';

    /**
     * @var Environment
     */
    protected static $environment;

    /**
     * {@inheritdoc}
     *
     * @throws LoaderError
     */
    public static function setUpBeforeClass(): void
    {
        $cachePath = sprintf('%s/%s/%s', __DIR__, static::CACHE_PATH, str_replace('\\', DIRECTORY_SEPARATOR, static::class));

        // remove temp files
        Filesystem::remove($cachePath);
        Filesystem::remove(sprintf('%s/%s/%s', __DIR__, static::RESULT_PATH, str_replace('\\', DIRECTORY_SEPARATOR, static::class)));

        // set up Twig environment
        $twigFileSystem = new FilesystemLoader([sprintf('%s/%s', __DIR__, static::RESOURCE_PATH)]);
        $twigFileSystem->addPath(sprintf('%s/%s', __DIR__, static::TEMPLATE_PATH), 'templates');

        static::$environment = new Environment($twigFileSystem, ['debug' => true, 'strict_variables' => true]);
        static::$environment->addExtension(new TwigSpreadsheetExtension([
            'pre_calculate_formulas' => true,
            'cache' => [
                'bitmap' => $cachePath.'/spreadsheet/bitmap',
                'xml' => false
            ],
        ]));
        static::$environment->setCache($cachePath.'/twig');
    }

    /**
     * @param string $templateName
     * @param string $format
     *
     * @throws SyntaxError
     * @throws LoaderError
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return Spreadsheet|string
     */
    protected function getDocument($templateName, $format)
    {
        $format = strtolower($format);

        // prepare global variables
        $request = new Request();
        $request->setRequestFormat($format);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $appVariable = new AppVariable();
        $appVariable->setRequestStack($requestStack);

        // generate source from template
        $source = static::$environment->load($templateName.'.twig')->render(['app' => $appVariable]);

        // create path
        $resultPath = sprintf('%s/%s/%s/%s.%s', __DIR__, static::RESULT_PATH, str_replace('\\', DIRECTORY_SEPARATOR, static::class), $templateName, $format);

        // save source
        Filesystem::dumpFile($resultPath, $source);

        // load source or return path for PDFs
        return $format === 'pdf' ? $resultPath : IOFactory::createReader(ucfirst($format))->load($resultPath);
    }
}
