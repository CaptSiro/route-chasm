<?php

namespace core\view_old;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\actions\Barrier;
use core\actions\IsLastAction;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\url\Url;
use models\Privilege\Privilege;
use RuntimeException;

class Component implements ViewTemplate, Action {
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

    public function createUrl(Path|string|null $relative = null): Url {
        if (!isset($this->routeNode)) {
            throw new RuntimeException('Route Node is not set. Cannot create URL.');
        }

        return Router::createUrlFromNode($this->routeNode, $relative);
    }

    public function performComponentAction(Request $request, Response $response): void {
        $response->render($this);
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