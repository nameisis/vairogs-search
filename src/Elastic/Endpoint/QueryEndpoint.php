<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Query\Compound\BoolQuery;
use Vairogs\Utils\Search\Elastic\Serializer\Normalizer\OrderedNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QueryEndpoint extends AbstractEndpoint implements OrderedNormalizerInterface
{
    public const NAME = 'query';

    /**
     * @var BoolQuery
     */
    private $bool;

    /**
     * @var bool
     */
    private $filtersSet = false;

    /**
     * {@inheritdoc}
     */
    public function add(BuilderInterface $builder, $key = null): string
    {
        return $this->addToBool($builder, BoolQuery::MUST, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function addToBool(BuilderInterface $builder, $boolType = null, $key = null): string
    {
        if (!$this->bool) {
            $this->bool = new BoolQuery();
        }

        return $this->bool->add($builder, $boolType, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($boolType = null): array
    {
        return $this->bool->getQueries($boolType);
    }

    /**
     * @return BoolQuery
     */
    public function getBool(): BuilderInterface
    {
        return $this->bool;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        if (!$this->filtersSet && $this->hasReference('filter_query')) {
            $filter = $this->getReference('filter_query');
            /** @var BuilderInterface $filter */
            $this->addToBool($filter, BoolQuery::FILTER);
            $this->filtersSet = true;
        }
        if (!$this->bool) {
            return null;
        }

        return $this->bool->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): int
    {
        return 2;
    }
}
