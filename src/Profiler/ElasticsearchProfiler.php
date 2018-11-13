<?php

namespace Vairogs\Utils\Search\Profiler;

use DateTimeInterface;
use Exception;
use Vairogs\Utils\Search\Profiler\Handler\CollectionHandler;
use LogicException;
use Monolog\Logger;
use Serializable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\VarDumper\Caster\CutStub;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

class ElasticsearchProfiler implements DataCollectorInterface, Serializable
{
    public const UNDEFINED_ROUTE = 'undefined_route';

    protected $data;
    protected $errors = [];

    /**
     * @var ClonerInterface
     */
    private $cloner;

    /**
     * @var Logger[]
     */
    private $loggers = [];

    /**
     * @var array
     */
    private $queries = [];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var float
     */
    private $time = 0;

    /**
     * @var array
     */
    private $managers = [];

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @param Logger $logger
     */
    public function addLogger(Logger $logger): void
    {
        $this->loggers[] = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, Exception $exception = null): void
    {
        foreach ($this->loggers as $logger) {
            /** @var Logger $logger */
            foreach ($logger->getHandlers() as $handler) {
                if ($handler instanceof CollectionHandler) {
                    $this->handleRecords($this->getRoute($request), $handler->getRecords());
                    $handler->clearRecords();
                }
            }
        }
    }

    /**
     * @param string $route
     * @param array $records
     */
    private function handleRecords($route, $records): void
    {
        $this->count += \count($records) / 2;
        $queryBody = '';
        foreach ($records as $record) {
            if (!empty($record['context'])) {
                $this->time += $record['context']['duration'];
                $this->addQuery($route, $record, $queryBody);
            } else {
                $position = \strpos($record['message'], ' -d');
                $queryBody = $position !== false ? \substr($record['message'], $position + 3) : '';
            }
        }
    }

    /**
     * @param string $route
     * @param array $record
     * @param string $queryBody
     */
    private function addQuery($route, $record, $queryBody): void
    {
        \parse_str(\parse_url($record['context']['uri'], \PHP_URL_QUERY), $httpParameters);
        $body = \json_decode(\trim($queryBody, " '\r\t\n"));
        $response = $record['context']['response'];
        $errors = [];
        if (empty($response)) {
            $response = null;
        }
        if (\is_string($response)) {
            $response = \json_decode($response, 1);
            $errors[] = $response;
        }
        $this->queries[$route][] = \array_merge([
            'body' => $body !== null ? \json_encode($body, \JSON_PRETTY_PRINT) : '',
            'method' => $record['context']['method'],
            'httpParameters' => $httpParameters,
            'time' => $record['context']['duration'] * 1000,
            'response' => $response !== null ? \json_encode($response, \JSON_PRETTY_PRINT) : '',
            'context' => $record['context'],
            'errors' => $errors,
        ], \array_diff_key(\parse_url($record['context']['uri']), \array_flip(['query'])));
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getRoute(Request $request): string
    {
        $route = $request->attributes->get('_route');

        return empty($route) ? self::UNDEFINED_ROUTE : $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'vairogs.utils.search.profiler';
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->queries = [];
        $this->count = 0;
        $this->time = 0;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return \round($this->time * 1000, 2);
    }

    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return array
     */
    public function getManagers(): array
    {
        if (\is_array(\reset($this->managers))) {
            foreach ($this->managers as $name => &$manager) {
                $manager = $name === 'default' ? 'default' : \sprintf('vairogs.utils.search.manager.%s', $name);
            }
            unset($manager);
        }

        return $this->managers;
    }

    /**
     * @param array $managers
     */
    public function setManagers($managers): void
    {
        $this->managers = $managers;
    }

    public function serialize(): string
    {
        return \serialize(\get_object_vars($this));
    }

    public function unserialize($data): void
    {
        if (($object = \unserialize($data, ['allowed_classes' => false])) !== null) {
            foreach ($object as $property => $value) {
                if (\property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        }
    }

    public function getData()
    {
        return $this->cloneVar($this);
    }

    protected function cloneVar($var)
    {
        if ($var instanceof Data) {
            return $var;
        }
        if (null === $this->cloner) {
            if (!\class_exists(CutStub::class)) {
                throw new LogicException(\sprintf('The %s component is needed for the %s() method. Install symfony/var-dumper version 3.4 or above.', VarDumper::class, __METHOD__));
            }
            $this->cloner = new VarCloner();
            $this->cloner->setMaxItems(-1);
            $this->cloner->addCasters($this->getCasters());
        }

        return $this->cloner->cloneVar($var);
    }

    /**
     * @return callable[]
     */
    protected function getCasters(): array
    {
        return [
            '*' => function($v, array $a) {
                if (!$v instanceof Stub) {
                    foreach ($a as $k => $v2) {
                        if (\is_object($v2) && !$v2 instanceof DateTimeInterface && !$v2 instanceof Stub) {
                            $a[$k] = new CutStub($v2);
                        }
                    }
                }

                return $a;
            },
        ];
    }
}
