<?php

namespace Vairogs\Utils\Search\Service;

use Elasticsearch\Client;
use Exception;
use InvalidArgumentException;
use Vairogs\Utils\Search\Event\BulkEvent;
use Vairogs\Utils\Search\Event\CommitEvent;
use Vairogs\Utils\Search\Event\Events;
use Vairogs\Utils\Search\Exception\BulkWithErrorsException;
use Vairogs\Utils\Search\Exception\DocumentParserException;
use Vairogs\Utils\Search\Mapping\MetadataCollector;
use Vairogs\Utils\Search\Result\Converter;
use LogicException;
use ReflectionException;
use stdClass;

class Manager
{
    use ManagerTrait;

    /**
     * @param string $name
     * @param array $config
     * @param Client $client
     * @param array $indexSettings
     * @param MetadataCollector $metadataCollector
     * @param Converter $converter
     */
    public function __construct($name, array $config, $client, array $indexSettings, $metadataCollector, $converter)
    {
        $this->name = $name;
        $this->config = $config;
        $this->client = $client;
        $this->indexSettings = $indexSettings;
        $this->metadataCollector = $metadataCollector;
        $this->converter = $converter;
    }

    /**
     * @param string
     *
     * @return Repository
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function getRepository($className): Repository
    {
        if (!\is_string($className)) {
            throw new InvalidArgumentException('Document class must be a string.');
        }
        $directory = null;
        if (\strpos($className, ':') !== false) {
            $bundle = \explode(':', $className)[0];
            if (isset($this->config['mappings'][$bundle]['document_dir'])) {
                $directory = $this->config['mappings'][$bundle]['document_dir'];
            }
        }
        $namespace = $this->getMetadataCollector()->getClassName($className, $directory);
        if (isset($this->repositories[$namespace])) {
            return $this->repositories[$namespace];
        }
        $repository = $this->createRepository($namespace);
        $this->repositories[$namespace] = $repository;

        return $repository;
    }

    /**
     * @param string $className
     *
     * @return Repository
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    private function createRepository($className): Repository
    {
        return new Repository($this, $className);
    }

    /**
     * @param array $types
     * @param array $query
     * @param array $queryStringParams
     *
     * @return array
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function search(array $types, array $query, array $queryStringParams = []): array
    {
        $params = [];
        $params['index'] = $this->getIndexName();

        $resolvedTypes = [];
        foreach ($types as $type) {
            $resolvedTypes[] = $this->resolveTypeName($type);
        }

        if (!empty($resolvedTypes)) {
            $params['type'] = \implode(',', $resolvedTypes);
        }

        $params['body'] = $query;
        if (!empty($queryStringParams)) {
            $params = \array_merge($queryStringParams, $params);
        }
        $this->stopwatch('start', 'search');
        try {
            $result = $this->getClient()->search($params);
        } catch (Exception $e) {
            $result = [];
        }
        $this->stopwatch('stop', 'search');

        return $result;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexSettings['index'];
    }

    /**
     * @param string $className
     *
     * @return string
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    private function resolveTypeName($className): string
    {
        if (\strpos($className, ':') !== false || \strpos($className, '\\') !== false) {
            return $this->getMetadataCollector()->getDocumentType($className);
        }

        return $className;
    }

    /**
     * @param string $action
     * @param string $name
     */
    private function stopwatch($action, $name): void
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->$action('vairogs_search: '.$name, 'vairogs_search');
        }
    }

    /**
     * @param $body
     *
     * @return array
     */
    public function msearch(array $body): array
    {
        try {
            return $this->getClient()->msearch([
                'index' => $this->getIndexName(),
                'body' => $body,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param stdClass $document
     *
     * @throws BulkWithErrorsException
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function persist($document): void
    {
        $documentArray = $this->converter->convertToArray($document);
        $type = $this->getMetadataCollector()->getDocumentType(\get_class($document));
        $this->bulk('index', $type, $documentArray);
    }

    /**
     * @param string $operation
     * @param string|array $type
     * @param array $query
     *
     * @return null|array
     * @throws BulkWithErrorsException
     */
    public function bulk($operation, $type, array $query): ?array
    {
        if (!\in_array($operation, ['index', 'create', 'update', 'delete'])) {
            throw new InvalidArgumentException('Wrong bulk operation selected');
        }
        $this->eventDispatcher->dispatch(Events::BULK, new BulkEvent($operation, $type, $query));
        $this->bulkQueries['body'][] = [
            $operation => \array_filter([
                '_type' => $type,
                '_id' => $query['_id'] ?? null,
                '_ttl' => $query['_ttl'] ?? null,
                '_routing' => $query['_routing'] ?? null,
                '_parent' => $query['_parent'] ?? null,
            ]),
        ];
        unset($query['_id'], $query['_ttl'], $query['_parent'], $query['_routing']);
        switch ($operation) {
            case 'index':
            case 'create':
            case 'update':
                $this->bulkQueries['body'][] = $query;
                break;
            case 'delete':
            default:
                break;
        }
        $this->bulkCount++;
        $response = null;
        if ($this->bulkCommitSize === $this->bulkCount) {
            $response = $this->commit();
        }

        return $response;
    }

    /**
     * @param array $params
     *
     * @return null|array
     *
     * @throws BulkWithErrorsException
     */
    public function commit(array $params = []): ?array
    {
        if (!empty($this->bulkQueries)) {
            $bulkQueries = \array_merge($this->bulkQueries, $this->bulkParams);
            $bulkQueries['index']['_index'] = $this->getIndexName();
            $this->eventDispatcher->dispatch(Events::PRE_COMMIT, new CommitEvent($this->getCommitMode(), $bulkQueries));
            $this->stopwatch('start', 'bulk');

            try {
                $bulkResponse = $this->getClient()->bulk($bulkQueries);
            } catch (Exception $e) {
                $bulkResponse = [];
            }
            $this->stopwatch('stop', 'bulk');
            if (empty($bulkResponse) || $bulkResponse['errors']) {
                throw new BulkWithErrorsException(\json_encode($bulkResponse), 0, null, $bulkResponse);
            }

            $this->bulkQueries = [];
            $this->bulkCount = 0;
            $this->stopwatch('start', 'refresh');
            switch ($this->getCommitMode()) {
                case 'flush':
                    $this->flush($params);
                    break;
                case 'refresh':
                    $this->refresh($params);
                    break;
            }
            $this->eventDispatcher->dispatch(Events::POST_COMMIT, new CommitEvent($this->getCommitMode(), $bulkResponse));
            $this->stopwatch('stop', 'refresh');

            return $bulkResponse;
        }

        return null;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function flush(array $params = []): array
    {
        try {
            return $this->getClient()->indices()->flush(\array_merge(['index' => $this->getIndexName()], $params));
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function refresh(array $params = []): array
    {
        try {
            return $this->getClient()->indices()->refresh(\array_merge(['index' => $this->getIndexName()], $params));
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param string $commitMode
     */
    public function setCommitMode($commitMode): void
    {
        if ($commitMode === 'refresh' || $commitMode === 'flush' || $commitMode === 'none') {
            $this->commitMode = $commitMode;
        } else {
            throw new LogicException('The commit method must be either refresh, flush or none.');
        }
    }

    /**
     * @param stdClass $document
     *
     * @throws BulkWithErrorsException
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function remove($document): void
    {
        $data = $this->converter->convertToArray($document, [], ['_id', '_routing']);
        if (!isset($data['_id'])) {
            throw new LogicException('In order to use remove() method document class must have property with @Id annotation.');
        }
        $type = $this->getMetadataCollector()->getDocumentType(\get_class($document));
        $this->bulk('delete', $type, $data);
    }

    /**
     * @param bool $noMapping
     *
     * @return array
     */
    public function dropAndCreateIndex($noMapping = false): array
    {
        try {
            if ($this->indexExists()) {
                $this->dropIndex();
            }
        } catch (Exception $e) {
            return [];
        }

        return $this->createIndex($noMapping);
    }

    /**
     * @return bool
     */
    public function indexExists(): bool
    {
        try {
            return $this->getClient()->indices()->exists(['index' => $this->getIndexName()]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function dropIndex(): array
    {
        try {
            return $this->getClient()->indices()->delete(['index' => $this->getIndexName()]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param bool $noMapping
     *
     * @return array
     */
    public function createIndex($noMapping = false): array
    {
        if ($noMapping) {
            unset($this->indexSettings['body']['mappings']);
        }

        try {
            return $this->getClient()->indices()->create($this->indexSettings);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param string $name
     */
    public function setIndexName($name): void
    {
        $this->indexSettings['index'] = $name;
    }

    /**
     * @return array
     */
    public function getIndexMappings(): array
    {
        return $this->indexSettings['body']['mappings'];
    }

    /**
     * @return string
     */
    public function getVersionNumber(): string
    {
        try {
            return $this->getClient()->info()['version']['number'];
        } catch (Exception $e) {
            return '';
        }
    }

    public function getInfo(): array
    {
        try {
            return $this->getClient()->info();
        } catch (Exception $e) {
            return [];
        }
    }

    public function clearCache(): void
    {
        try {
            $this->getClient()->indices()->clearCache(['index' => $this->getIndexName()]);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param string $className
     * @param string $id
     * @param string $routing
     *
     * @return stdClass
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function find($className, $id, $routing = null): stdClass
    {
        $type = $this->resolveTypeName($className);
        $params = [
            'index' => $this->getIndexName(),
            'type' => $type,
            'id' => $id,
        ];
        if ($routing) {
            $params['routing'] = $routing;
        }

        $result = null;
        try {
            return $this->getConverter()->convertToDocument($this->getClient()->get($params), $this);
        } catch (Exception $e) {
            return new stdClass();
        }
    }

    /**
     * @param string $scrollId
     * @param string $scrollDuration
     *
     * @return array
     *
     * @throws Exception
     */
    public function scroll($scrollId, $scrollDuration = '5m'): array
    {
        try {
            return $this->getClient()->scroll(['scroll_id' => $scrollId, 'scroll' => $scrollDuration]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param string $scrollId
     */
    public function clearScroll($scrollId): void
    {
        try {
            $this->getClient()->clearScroll(['scroll_id' => $scrollId]);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        try {
            return $this->getClient()->indices()->getSettings(['index' => $this->getIndexName()]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getAliases(array $params = []): array
    {
        try {
            return $this->getClient()->indices()->getAliases(\array_merge(['index' => $this->getIndexName()], $params));
        } catch (Exception $e) {
            return [];
        }
    }
}
