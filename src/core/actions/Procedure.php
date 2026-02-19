<?php

namespace core\actions;

use Closure;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;
use core\view\View;

class Procedure implements Action {
    use ActionBindRouteNode, ActorClassName;

    public static function middleware(Closure $closure): static {
        return new static($closure, true);
    }

    /**
     * Maps all the elements of the given array of actions or functions and return array containing only actions. All the
     * functions are wrapped in Procedure object. Parameter 'isMiddleware' is passed to all the Procedure constructors.
     *
     * @param array<Action|Closure> $actions
     * @param bool $isMiddleware
     *
     * @return array<Action>
     */
    public static function resolve(array &$actions, bool $isMiddleware = false): array {
        foreach ($actions as $i => $action) {
            if ($action instanceof Action) {
                continue;
            }

            $actions[$i] = new Procedure($action, $isMiddleware);
        }

        return $actions;
    }



    public function __construct(
        protected Closure $closure,
        protected bool $isMiddleware = false,
    ) {}



    public function setIsMiddleware(bool $isMiddleware): static {
        $this->isMiddleware = $isMiddleware;
        return $this;
    }



    // Action
    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $ret = ($this->closure)($request, $response);

        if ($ret instanceof Action) {
            $ret->perform($request, $response);
        }

        if ($ret instanceof View) {
            $response->renderRoot($ret);
        }
    }
}