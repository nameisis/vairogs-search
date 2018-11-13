<?php

namespace Vairogs\Utils\Search\Event;

final class Events
{
    public const BULK = 'vairogs.utils.search.bulk';
    public const PRE_COMMIT = 'vairogs.utils.search.pre_commit';
    public const POST_COMMIT = 'vairogs.utils.search.post_commit';
    public const PRE_MANAGER_CREATE = 'vairogs.utils.search.pre_manager_create';
    public const POST_MANAGER_CREATE = 'vairogs.utils.search.post_manager_create';
}
