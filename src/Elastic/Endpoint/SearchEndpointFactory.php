<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use RuntimeException;

class SearchEndpointFactory
{
    /**
     * @var array
     */
    private static $ENDPOINTS = [
        'query' => QueryEndpoint::class,
        'post_filter' => PostFilterEndpoint::class,
        'sort' => SortEndpoint::class,
        'highlight' => HighlightEndpoint::class,
        'aggregations' => AggregationsEndpoint::class,
        'suggest' => SuggestEndpoint::class,
        'inner_hits' => InnerHitsEndpoint::class,
    ];

    /**
     * @param string $type
     *
     * @return EndpointInterface
     *
     * @throws RuntimeException
     */
    public static function get($type): EndpointInterface
    {
        if (!\array_key_exists($type, self::$ENDPOINTS)) {
            throw new RuntimeException('Endpoint does not exist.');
        }

        return new self::$ENDPOINTS[$type]();
    }
}
