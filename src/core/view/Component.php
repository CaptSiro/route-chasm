<?php

namespace core\view;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\actions\Barrier;
use core\actions\IsLastAction;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\locale\LexiconUnit;
use core\route\RouteNode;
use models\core\Privilege\Privilege;

class Component implements View, Action {
    use Renderer, ActionBindRouteNode, ActorClassName, IsLastAction, LexiconUnit, Barrier;



    public function __construct(
        protected bool $isMiddleware = false
    ) {}

    public function __toString(): string {
        return $this->render();
    }



    // Action
    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function performComponentAction(Request $request, Response $response): void {
        $response->renderRoot($this);
    }

    public function perform(Request $request, Response $response): void {
        if (!$this->isLastAction($request)) {
            return;
        }

        if (!$this->hasRequestAccess(Privilege::fromName(Privilege::READ), $request)) {
            $response->sendStatus(HttpCode::CE_FORBIDDEN);
        }

        $this->performComponentAction($request, $response);
    }
}