<?php

namespace core\actions;

use core\route\RouteNode;

abstract class Controller implements Action {
    use ActionBindRouteNode, ActorClassName;



    public function __construct(
        protected bool $isMiddleware = false
    ) {}



    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }
}