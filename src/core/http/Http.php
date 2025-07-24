<?php

namespace core\http;

use Closure;
use core\actions\Action;
use core\actions\Procedure;

class Http {
    /**
     * @param array<Action|Closure> $actions
     * @return array<Action>
     */
    protected static function resolve(array &$actions): array {
        foreach ($actions as $i => $action) {
            if ($action instanceof Closure) {
                $actions[$i] = new Procedure($action);
            }
        }

        return $actions;
    }

    public static function connect(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::CONNECT))
            ->setActions(self::resolve($endpoints));
    }

    public static function delete(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::DELETE))
            ->setActions(self::resolve($endpoints));
    }

    public static function get(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::GET))
            ->setActions(self::resolve($endpoints));
    }

    public static function head(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::HEAD))
            ->setActions(self::resolve($endpoints));
    }

    public static function options(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::OPTIONS))
            ->setActions(self::resolve($endpoints));
    }

    public static function patch(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::PATCH))
            ->setActions(self::resolve($endpoints));
    }

    public static function post(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::POST))
            ->setActions(self::resolve($endpoints));
    }

    public static function put(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::PUT))
            ->setActions(self::resolve($endpoints));
    }

    public static function trace(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::TRACE))
            ->setActions(self::resolve($endpoints));
    }

    public static function any(Action|Closure ...$endpoints): HttpGate {
        return (new HttpGate(HttpMethod::ANY))
            ->setActions(self::resolve($endpoints));
    }
}