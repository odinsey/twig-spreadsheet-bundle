<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;
use Twig\Environment;

class HeaderFooterWrapper extends BaseWrapper
{
    const ALIGNMENT_CENTER = 'center';
    const ALIGNMENT_LEFT = 'left';
    const ALIGNMENT_RIGHT = 'right';

    const BASETYPE_FOOTER = 'footer';
    const BASETYPE_HEADER = 'header';

    const TYPE_EVEN = 'even';
    const TYPE_FIRST = 'first';
    const TYPE_ODD = 'odd';

    protected SheetWrapper $sheetWrapper;
    protected ?HeaderFooter $object = null;
    protected array $alignmentParameters = [];

    public function __construct(array $context, Environment $environment, SheetWrapper $sheetWrapper)
    {
        parent::__construct($context, $environment);

        $this->sheetWrapper = $sheetWrapper;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateAlignment(string $alignment): string
    {
        if (!in_array($alignment, [self::ALIGNMENT_CENTER, self::ALIGNMENT_LEFT, self::ALIGNMENT_RIGHT], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown alignment "%s"', $alignment));
        }

        return $alignment;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateBaseType(string $baseType): string
    {
        if (!in_array($baseType, [self::BASETYPE_FOOTER, self::BASETYPE_HEADER], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown base type "%s"', $baseType));
        }

        return $baseType;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function start(string $baseType, ?string $type = null, array $properties = []): void
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }

        if ($type !== null) {
            $type = strtolower($type);

            if (!in_array($type, [self::TYPE_EVEN, self::TYPE_FIRST, self::TYPE_ODD], true)) {
                throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
            }
        }

        $this->object = $this->sheetWrapper->getObject()->getHeaderFooter();
        $this->parameters['baseType'] = self::validateBaseType(strtolower($baseType));
        $this->parameters['type'] = $type;
        $this->parameters['properties'] = $properties;
        $this->parameters['value'] = [self::ALIGNMENT_LEFT => null, self::ALIGNMENT_CENTER => null, self::ALIGNMENT_RIGHT => null];

        $this->setProperties($properties);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function end(): void
    {
        if ($this->object === null) {
            throw new \LogicException();
        }

        $value = implode('', $this->parameters['value']);

        switch ($this->parameters['type']) {
            case null:
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setOddHeader($value);
                    $this->object->setEvenHeader($value);
                    $this->object->setFirstHeader($value);
                } else {
                    $this->object->setOddFooter($value);
                    $this->object->setEvenFooter($value);
                    $this->object->setFirstFooter($value);
                }
                break;
            case self::TYPE_EVEN:
                $this->object->setDifferentOddEven(true);
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setEvenHeader($value);
                } else {
                    $this->object->setEvenFooter($value);
                }
                break;
            case self::TYPE_FIRST:
                $this->object->setDifferentFirst(true);
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setFirstHeader($value);
                } else {
                    $this->object->setFirstFooter($value);
                }
                break;
            case self::TYPE_ODD:
                $this->object->setDifferentOddEven(true);
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setOddHeader($value);
                } else {
                    $this->object->setOddFooter($value);
                }
                break;
        }

        $this->object = null;
        $this->parameters = [];
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function startAlignment(string $alignment, array $properties = []): void
    {
        if ($this->object === null) {
            throw new \LogicException();
        }

        $alignment = self::validateAlignment(strtolower($alignment));

        $this->alignmentParameters['type'] = $alignment;
        $this->alignmentParameters['properties'] = $properties;

        switch ($alignment) {
            case self::ALIGNMENT_LEFT:
                $this->parameters['value'][self::ALIGNMENT_LEFT] = '&L';
                break;
            case self::ALIGNMENT_CENTER:
                $this->parameters['value'][self::ALIGNMENT_CENTER] = '&C';
                break;
            case self::ALIGNMENT_RIGHT:
                $this->parameters['value'][self::ALIGNMENT_RIGHT] = '&R';
                break;
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function endAlignment(mixed $value): void
    {
        if ($this->object === null || !isset($this->alignmentParameters['type'])) {
            throw new \LogicException();
        }

        if (strpos($this->parameters['value'][$this->alignmentParameters['type']], '&G') === false) {
            $this->parameters['value'][$this->alignmentParameters['type']] .= $value;
        }

        $this->alignmentParameters = [];
    }

    public function getObject(): ?HeaderFooter
    {
        return $this->object;
    }

    public function setObject(?HeaderFooter $object = null): void
    {
        $this->object = $object;
    }

    public function getAlignmentParameters(): array
    {
        return $this->alignmentParameters;
    }

    public function setAlignmentParameters(array $alignmentParameters): void
    {
        $this->alignmentParameters = $alignmentParameters;
    }

    protected function configureMappings(): array
    {
        return [
            'scaleWithDocument' => function ($value) { $this->object->setScaleWithDocument($value); },
            'alignWithMargins' => function ($value) { $this->object->setAlignWithMargins($value); },
        ];
    }
}
