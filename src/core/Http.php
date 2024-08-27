<?php

namespace core;

use Closure;
use core\endpoints\Endpoint;
use core\endpoints\Handler;
use core\endpoints\Procedure;

class Http {
    /**
     * @param array<Endpoint|Closure> $endpoints
     * @return array<Endpoint>
     */
    protected static function createHandles(array &$endpoints): array {
        foreach ($endpoints as $i => $endpoint) {
            if ($endpoint instanceof Closure) {
                $endpoints[$i] = new Procedure($endpoint);
            }
        }

        return $endpoints;
    }

    public static function connect(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("CONNECT"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function delete(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("DELETE"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function get(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("GET"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function head(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("HEAD"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function options(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("OPTIONS"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function patch(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("PATCH"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function post(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("POST"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function put(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("PUT"))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function trace(Endpoint|Closure $enpoint, Endpoint|Closure ...$endpoints): Handler {
        return (new Handler("TRACE"))
            ->setHandles(self::createHandles($endpoints));
    }
}