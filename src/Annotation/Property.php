<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Property
{
    /**
     * @var string
     *
     * @Required
     * @Enum({
     *     "text", "keyword",
     *     "long", "integer", "short", "byte", "double", "float",
     *     "date",
     *     "boolean",
     *     "binary",
     *     "geo_point", "geo_shape",
     *     "ip", "completion", "token_count", "murmur3", "attachments", "percolator"
     * })
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function dump(array $exclude = []): array
    {
        return \array_diff_key(\array_merge([
            'type' => $this->type,
        ], $this->options), $exclude);
    }
}
