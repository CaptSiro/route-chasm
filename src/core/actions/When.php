<?php

namespace core\actions;

use Closure;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;

class When implements Action {
    use ActionBindRouteNode;



    public function __construct(
        protected Closure $condition,
        protected Action $success,
        protected ?Action $failure = null
    ) {}



    public function isMiddleware(): bool {
        return $this->success->isMiddleware();
    }

    public function getActorName(): string {
        return "When(". $this->success->getActorName() .")";
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        if (!($this->condition)($request, $response)) {
            $this->failure?->perform($request, $response);
            return;
        }

        $this->success->perform($request, $response);
    }
}