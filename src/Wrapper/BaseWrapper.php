<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use Twig\Environment;

abstract class BaseWrapper
{
    protected array $context;
    protected Environment $environment;
    protected array $parameters;
    protected array $mappings;

    public function __construct(array $context, Environment $environment)
    {
        $this->context = $context;
        $this->environment = $environment;

        $this->parameters = [];
        $this->mappings = $this->configureMappings();
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function setMappings(array $mappings): void
    {
        $this->mappings = $mappings;
    }

    protected function configureMappings(): array
    {
        return [];
    }

    /**
     * Calls the matching mapping callable for each property.
     *
     * @throws \RuntimeException
     */
    protected function setProperties(array $properties, ?array $mappings = null, ?string $column = null): void
    {
        if ($mappings === null) {
            $mappings = $this->mappings;
        }

        foreach ($properties as $key => $value) {
            if (!isset($mappings[$key])) {
                throw new \RuntimeException(sprintf('Missing mapping for key "%s"', $key));
            }

            if (is_array($value) && is_array($mappings[$key])) {
                // recursion
                if (isset($mappings[$key]['__multi'])) {
                    foreach ($value as $_column => $_value) {
                        $this->setProperties($_value, $mappings[$key], $_column);
                    }
                } else {
                    $this->setProperties($value, $mappings[$key]);
                }
            } elseif (is_callable($mappings[$key])) {
                $mappings[$key](
                    $value,
                    $column !== null ? $mappings['__multi']($column) : null
                );
            } else {
                throw new \RuntimeException(sprintf('Invalid mapping for key "%s"', $key));
            }
        }
    }
}
