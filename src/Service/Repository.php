<?php

namespace Vairogs\Utils\Search\Service;

use InvalidArgumentException;
use Vairogs\Utils\Search\Elastic\FieldSort;
use Vairogs\Utils\Search\Elastic\Query\Compound\BoolQuery;
use Vairogs\Utils\Search\Elastic\Query\FullText\QueryStringQuery;
use Vairogs\Utils\Search\Elastic\Search;
use Vairogs\Utils\Search\Exception\DocumentParserException;
use Vairogs\Utils\Search\Result\ArrayIterator;
use Vairogs\Utils\Search\Result\DocumentIterator;
use Vairogs\Utils\Search\Result\RawIterator;
use LogicException;
use ReflectionException;
use stdClass;

class Repository
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $type;

    /**
     * @param Manager $manager
     * @param string $className
     *
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function __construct($manager, $className)
    {
        if (!\is_string($className)) {
            throw new InvalidArgumentException('Class name must be a string.');
        }
        if (!\class_exists($className)) {
            throw new InvalidArgumentException(\sprintf('Cannot create repository for non-existing class "%s".', $className));
        }
        $this->manager = $manager;
        $this->className = $className;
        $this->type = $this->resolveType($className);
    }

    /**
     * @param string $className
     *
     * @return array|string
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    private function resolveType($className): array
    {
        return $this->getManager()->getMetadataCollector()->getDocumentType($className);
    }

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * @param string $id
     * @param string $routing
     *
     * @return stdClass
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function find($id, $routing = null): stdClass
    {
        return $this->manager->find($this->type, $id, $routing);
    }

    /**
     * @param array $ids
     *
     * @return DocumentIterator The objects.
     */
    public function findByIds(array $ids): DocumentIterator
    {
        $args = [];
        $manager = $this->getManager();
        $args['body']['docs'] = [];
        $args['index'] = $manager->getIndexName();
        $args['type'] = $this->getType();
        foreach ($ids as $id) {
            $args['body']['docs'][] = [
                '_id' => $id,
            ];
        }
        $mgetResponse = $manager->getClient()->mget($args);
        $return = [
            'hits' => [
                'hits' => [],
                'total' => 0,
            ],
        ];
        foreach ($mgetResponse['docs'] as $item) {
            if ($item['found']) {
                $return['hits']['hits'][] = $item;
            }
        }
        $return['hits']['total'] = \count($return['hits']['hits']);

        return new DocumentIterator($return, $manager);
    }

    /**
     * @return array
     */
    public function getType(): array
    {
        return $this->type;
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     *
     * @return stdClass|null The object.
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function findOneBy(array $criteria, array $orderBy = []): ?stdClass
    {
        return $this->findBy($criteria, $orderBy, 1, null)->current();
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|DocumentIterator The objects.
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
    {
        $search = $this->createSearch();
        if ($limit !== null) {
            $search->setSize($limit);
        }
        if ($offset !== null) {
            $search->setFrom($offset);
        }
        foreach ($criteria as $field => $value) {
            if (\preg_match('/^!(.+)$/', $field)) {
                $boolType = BoolQuery::MUST_NOT;
                $field = \preg_replace('/^!/', '', $field);
            } else {
                $boolType = BoolQuery::MUST;
            }
            $search->addQuery(new QueryStringQuery(\is_array($value) ? \implode(' OR ', $value) : $value, ['default_field' => $field]), $boolType);
        }
        foreach ($orderBy as $field => $direction) {
            $search->addSort(new FieldSort($field, $direction));
        }

        return $this->findDocuments($search);
    }

    /**
     * @return Search
     */
    public function createSearch(): Search
    {
        return new Search();
    }

    /**
     * @param Search $search
     *
     * @return DocumentIterator
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function findDocuments(Search $search): DocumentIterator
    {
        $results = $this->executeSearch($search);

        return new DocumentIterator($results, $this->getManager(), $this->getScrollConfiguration($results, $search->getScroll()));
    }

    /**
     * @param Search $search
     *
     * @return array
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    private function executeSearch(Search $search): array
    {
        return $this->getManager()->search([$this->getType()], $search->toArray(), $search->getUriParams());
    }

    /**
     * @param array $raw
     * @param string $scrollDuration
     *
     * @return array
     */
    public function getScrollConfiguration($raw, $scrollDuration): array
    {
        $scrollConfig = [];
        if (isset($raw['_scroll_id'])) {
            $scrollConfig['_scroll_id'] = $raw['_scroll_id'];
            $scrollConfig['duration'] = $scrollDuration;
        }

        return $scrollConfig;
    }

    /**
     * @param Search $search
     *
     * @return ArrayIterator
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function findArray(Search $search): ArrayIterator
    {
        $results = $this->executeSearch($search);

        return new ArrayIterator($results, $this->getManager(), $this->getScrollConfiguration($results, $search->getScroll()));
    }

    /**
     * @param Search $search
     *
     * @return RawIterator
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function findRaw(Search $search): RawIterator
    {
        $results = $this->executeSearch($search);

        return new RawIterator($results, $this->getManager(), $this->getScrollConfiguration($results, $search->getScroll()));
    }

    /**
     * @param Search $search
     * @param array $params
     * @param bool $returnRaw
     *
     * @return int|array
     */
    public function count(Search $search, array $params = [], $returnRaw = false)
    {
        $body = \array_merge([
            'index' => $this->getManager()->getIndexName(),
            'type' => $this->type,
            'body' => $search->toArray(),
        ], $params);
        $results = $this->getManager()->getClient()->count($body);
        if ($returnRaw) {
            return $results;
        }

        return $results['count'];
    }

    /**
     * @param string $id
     * @param string $routing
     *
     * @return array
     *
     * @throws LogicException
     */
    public function remove($id, $routing = null): array
    {
        $params = [
            'index' => $this->getManager()->getIndexName(),
            'type' => $this->type,
            'id' => $id,
        ];
        if ($routing) {
            $params['routing'] = $routing;
        }

        return $this->getManager()->getClient()->delete($params);
    }

    /**
     * @param string $id
     * @param array $fields
     * @param string $script
     * @param array $params
     *
     * @return array
     */
    public function update($id, array $fields = [], $script = null, array $params = []): array
    {
        $body = \array_filter([
            'doc' => $fields,
            'script' => $script,
        ]);
        $params = \array_merge([
            'id' => $id,
            'index' => $this->getManager()->getIndexName(),
            'type' => $this->type,
            'body' => $body,
        ], $params);

        return $this->getManager()->getClient()->update($params);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
