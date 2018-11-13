<?php

namespace Vairogs\Utils\Search\Elastic;

use InvalidArgumentException;
use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\SearchTrait;
use Vairogs\Utils\Search\Elastic\Endpoint\AbstractEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\AggregationsEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\EndpointInterface;
use Vairogs\Utils\Search\Elastic\Endpoint\HighlightEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\InnerHitsEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\PostFilterEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\QueryEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\SearchEndpointFactory;
use Vairogs\Utils\Search\Elastic\Endpoint\SortEndpoint;
use Vairogs\Utils\Search\Elastic\Endpoint\SuggestEndpoint;
use Vairogs\Utils\Search\Elastic\InnerHit\NestedInnerHit;
use Vairogs\Utils\Search\Elastic\Query\Compound\BoolQuery;
use Vairogs\Utils\Search\Elastic\Serializer\Normalizer\CustomReferencedNormalizer;
use Vairogs\Utils\Search\Elastic\Serializer\OrderedSerializer;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

class Search
{
    use SearchTrait;

    public function __construct()
    {
        $this->serializer = new OrderedSerializer([
            new CustomReferencedNormalizer(),
            new CustomNormalizer(),
        ]);
    }

    /**
     * @param string $type
     */
    public function destroyEndpoint($type): void
    {
        unset($this->endpoints[$type]);
    }

    /**
     * @param BuilderInterface $query
     * @param string $boolType
     * @param string $key
     *
     * @return $this
     */
    public function addQuery(BuilderInterface $query, $boolType = BoolQuery::MUST, $key = null): Search
    {
        $endpoint = $this->getEndpoint(QueryEndpoint::NAME);
        $endpoint->addToBool($query, $boolType, $key);

        return $this;
    }

    /**
     * @param string $type Endpoint type.
     *
     * @return EndpointInterface
     */
    private function getEndpoint($type): EndpointInterface
    {
        if (!\array_key_exists($type, $this->endpoints)) {
            $this->endpoints[$type] = SearchEndpointFactory::get($type);
        }

        return $this->endpoints[$type];
    }

    /**
     * @return BuilderInterface
     */
    public function getQueries(): BuilderInterface
    {
        return $this->getEndpoint(QueryEndpoint::NAME)->getBool();
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setQueryParameters(array $parameters): Search
    {
        $this->setEndpointParameters(QueryEndpoint::NAME, $parameters);

        return $this;
    }

    /**
     * @param string $endpointName
     * @param array $parameters
     */
    public function setEndpointParameters($endpointName, array $parameters): void
    {
        $endpoint = $this->getEndpoint($endpointName);
        /** @var AbstractEndpoint $endpoint */
        $endpoint->setParameters($parameters);
    }

    /**
     * @param BuilderInterface $filter
     * @param string $boolType
     * @param string $key
     *
     * @return $this
     */
    public function addPostFilter(BuilderInterface $filter, $boolType = BoolQuery::MUST, $key = null): Search
    {
        $this->getEndpoint(PostFilterEndpoint::NAME)->addToBool($filter, $boolType, $key);

        return $this;
    }

    /**
     * @return BuilderInterface
     */
    public function getPostFilters(): BuilderInterface
    {
        return $this->getEndpoint(PostFilterEndpoint::NAME)->getBool();
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setPostFilterParameters(array $parameters): Search
    {
        $this->setEndpointParameters(PostFilterEndpoint::NAME, $parameters);

        return $this;
    }

    /**
     * @param AbstractAggregation $aggregation
     *
     * @return $this
     */
    public function addAggregation(AbstractAggregation $aggregation): Search
    {
        $this->getEndpoint(AggregationsEndpoint::NAME)->add($aggregation, $aggregation->getName());

        return $this;
    }

    /**
     * @return BuilderInterface[]
     */
    public function getAggregations(): array
    {
        return $this->getEndpoint(AggregationsEndpoint::NAME)->getAll();
    }

    /**
     * @param NestedInnerHit $innerHit
     *
     * @return $this
     */
    public function addInnerHit(NestedInnerHit $innerHit): Search
    {
        $this->getEndpoint(InnerHitsEndpoint::NAME)->add($innerHit, $innerHit->getName());

        return $this;
    }

    /**
     * @return BuilderInterface[]
     */
    public function getInnerHits(): array
    {
        return $this->getEndpoint(InnerHitsEndpoint::NAME)->getAll();
    }

    /**
     * @param BuilderInterface $sort
     *
     * @return $this
     */
    public function addSort(BuilderInterface $sort): Search
    {
        $this->getEndpoint(SortEndpoint::NAME)->add($sort);

        return $this;
    }

    /**
     * @return BuilderInterface[]
     */
    public function getSorts(): array
    {
        return $this->getEndpoint(SortEndpoint::NAME)->getAll();
    }

    /**
     * @param Highlight $highlight
     *
     * @return $this
     */
    public function addHighlight($highlight): Search
    {
        $this->getEndpoint(HighlightEndpoint::NAME)->add($highlight);

        return $this;
    }

    /**
     * @return BuilderInterface
     */
    public function getHighlights(): BuilderInterface
    {
        $highlightEndpoint = $this->getEndpoint(HighlightEndpoint::NAME);

        /** @var HighlightEndpoint $highlightEndpoint */

        return $highlightEndpoint->getHighlight();
    }

    /**
     * @param BuilderInterface $suggest
     *
     * @return $this
     */
    public function addSuggest(BuilderInterface $suggest): Search
    {
        $this->getEndpoint(SuggestEndpoint::NAME)->add($suggest, SuggestEndpoint::NAME);

        return $this;
    }

    /**
     * @return BuilderInterface[]
     */
    public function getSuggests(): array
    {
        return $this->getEndpoint(SuggestEndpoint::NAME)->getAll();
    }

    /**
     * @param string $scroll
     *
     * @return $this
     */
    public function setScroll($scroll = '5m'): Search
    {
        $this->scroll = $scroll;
        $this->addUriParam('scroll', $this->scroll);

        return $this;
    }

    /**
     * @param string $name
     * @param string|array|bool $value
     *
     * @return $this
     */
    public function addUriParam($name, $value): Search
    {
        if (\in_array($name, [
            'q',
            'df',
            'analyzer',
            'analyze_wildcard',
            'default_operator',
            'lenient',
            'explain',
            '_source',
            '_source_exclude',
            '_source_include',
            'stored_fields',
            'sort',
            'track_scores',
            'timeout',
            'terminate_after',
            'from',
            'size',
            'search_type',
            'scroll',
            'allow_no_indices',
            'ignore_unavailable',
            'typed_keys',
            'pre_filter_shard_size',
            'ignore_unavailable',
        ])) {
            $this->uriParams[$name] = $value;
        } else {
            throw new InvalidArgumentException(\sprintf('Parameter %s is not supported.', $name));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getUriParams(): array
    {
        return $this->uriParams;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $output = $this->serializer->normalize($this->endpoints);
        if (\is_array($output)) {
            $output = \array_filter($output);
        }
        $params = [
            'from' => 'from',
            'size' => 'size',
            'source' => '_source',
            'storedFields' => 'stored_fields',
            'scriptFields' => 'script_fields',
            'docValueFields' => 'docvalue_fields',
            'explain' => 'explain',
            'version' => 'version',
            'indicesBoost' => 'indices_boost',
            'minScore' => 'min_score',
            'searchAfter' => 'search_after',
        ];
        foreach ($params as $field => $param) {
            if ($this->$field !== null) {
                $output[$param] = $this->$field;
            }
        }

        return $output;
    }
}
