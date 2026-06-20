<?php

namespace core\communication\body;

use core\communication\Request;

trait RequestBodyCache {
    /** @var array<int, static> */
    private static array $cache = [];



    protected function bodyCache_get(Request $request): ?static {
        return self::$cache[$request->getInstanceId()] ?? null;
    }

    protected function bodyCache_set(Request $request, self $instance): ?static {
        return self::$cache[$request->getInstanceId()] = $instance;
    }
}