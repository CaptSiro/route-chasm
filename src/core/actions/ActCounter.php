<?php

namespace core\actions;

use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;

class ActCounter implements Action {
    use BindRouteNode;



    public function __construct(
        protected int $n = 0,
        protected bool $isMiddleware = false,
    ) {}



    public function getN(): int {
        return $this->n;
    }


    // Action
    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function getActorName(): string {
        return 'ActCounter';
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function act(Request $request, Response $response): void {
        $this->n++;
    }
}