<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Vairogs\Utils\Core\Util\Text;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Embedded
{
    /**
     * @var string
     *
     * @Required
     */
    public $class;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $multiple;

    /**
     * @var array
     */
    public $options;

    public function dump(array $exclude = []): array
    {
        $array = \array_diff_key(\array_filter(\get_object_vars($this), function($value) {
            return $value || \is_bool($value);
        }), \array_flip(\array_merge(['class', 'name', 'multiple'], $exclude)));

        return \array_combine(\array_map(function($key) {
            return Text::toSnakeCase($key);
        }, \array_keys($array)), \array_values($array));
    }
}
