<?php

namespace tests\utils\RouteChasm\actions;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;
use sptf\Sptf;

class MatchRemainingPath implements Action {
    use ActionBindRouteNode, ActorClassName;



    protected bool $performed = false;

    public function __construct(
        protected string $expected,
        protected bool $isMiddleware = false
    ) {}



    public function isPerformed(): bool {
        return $this->performed;
    }

    public function setPerformed(bool $performed): void {
        $this->performed = $performed;
    }

    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $this->performed = true;
        Sptf::expect($request->getRemainingPath()->toString())
            ->toBe($this->expected);
    }
}