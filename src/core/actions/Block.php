<?php

namespace core\actions;

use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\route\RouteNode;
use models\core\Privilege\Privilege;
use models\core\User\User;
use models\core\UserResource;

class Block implements Action {
    use ActionBindRouteNode, ActorClassName;



    public function __construct(
        protected UserResource $resource,
        protected Privilege $privilege,
        protected $isMiddleware = true
    ) {}



    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $user = User::fromRequest($request);
        if ($user->hasAccess($this->resource, $this->privilege)) {
            return;
        }

        $response->sendStatus(HttpCode::CE_FORBIDDEN);
    }
}