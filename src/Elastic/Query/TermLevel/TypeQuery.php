<?php

namespace Vairogs\Utils\Search\Elastic\Query\TermLevel;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;

/**
 * @link https://goo.gl/RzpA6H
 */
class TypeQuery implements BuilderInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'type';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            $this->getType() => [
                'value' => $this->type,
            ],
        ];
    }
}
