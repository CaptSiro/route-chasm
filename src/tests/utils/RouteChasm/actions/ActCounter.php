<?php

namespace tests\utils\RouteChasm\actions;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;

class ActCounter implements Action {
    use ActionBindRouteNode;



    public function __construct(
        protected string $name,
        protected int $n = 0,
        protected bool $isMiddleware = false,
    ) {}



    public function getN(): int {
        return $this->n;
    }

    public function setN(int $n): void {
        $this->n = $n;
    }


    // Action
    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function getActorName(): string {
        return 'ActCounter:'. $this->name;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $this->n++;
    }
}