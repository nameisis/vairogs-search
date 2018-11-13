<?php

namespace Vairogs\Utils\Search\Elastic\Query\Specialized;

use InvalidArgumentException;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/CWcmxS
 */
class TemplateQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $inline;

    /**
     * @var array
     */
    private $params;

    /**
     * @param string $file
     * @param string $inline
     * @param array $params
     */
    public function __construct($file = null, $inline = null, array $params = [])
    {
        $this->setFile($file);
        $this->setInline($inline);
        $this->setParams($params);
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getInline(): string
    {
        return $this->inline;
    }

    /**
     * @param string $inline
     */
    public function setInline($inline): void
    {
        $this->inline = $inline;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params): void
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'template';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $output = \array_filter([
            'file' => $this->getFile(),
            'inline' => $this->getInline(),
            'params' => $this->getParams(),
        ]);
        if (!isset($output['file'], $output['inline'])) {
            throw new InvalidArgumentException('Template query requires that either `inline` or `file` parameters are set');
        }
        $output = $this->processArray($output);

        return [$this->getType() => $output];
    }
}
