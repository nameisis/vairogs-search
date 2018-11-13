<?php

namespace Vairogs\Utils\Search\Elastic;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use stdClass;

class Highlight implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @param string $name
     * @param array $params
     *
     * @return $this
     */
    public function addField($name, array $params = []): self
    {
        $this->fields[$name] = $params;

        return $this;
    }

    /**
     * @param array $preTags
     * @param array $postTags
     *
     * @return $this
     */
    public function setTags(array $preTags, array $postTags): self
    {
        $this->tags['pre_tags'] = $preTags;
        $this->tags['post_tags'] = $postTags;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'highlight';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $output = $this->processArray($this->tags);
        foreach ($this->fields as $field => $params) {
            $output['fields'][$field] = \count($params) ? $params : new stdClass();
        }

        return $output;
    }
}
