<?php

namespace core\http;

use Closure;
use core\endpoints\Endpoint;
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

    public static function connect(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::CONNECT))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function delete(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::DELETE))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function get(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::GET))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function head(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::HEAD))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function options(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::OPTIONS))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function patch(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::PATCH))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function post(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::POST))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function put(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::PUT))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function trace(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::TRACE))
            ->setEndpoints(self::createHandles($endpoints));
    }

    public static function any(Endpoint|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::ANY))
            ->setEndpoints(self::createHandles($endpoints));
    }
}