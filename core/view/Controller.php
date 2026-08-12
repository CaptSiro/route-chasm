<?php

namespace core\view;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\actions\Barrier;
use core\actions\Block;
use core\actions\IsLastAction;
use core\actions\UserResourceBarrier;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\url\Url;
use models\Privilege\Privilege;
use RuntimeException;

class Controller extends Component implements Action, UserResourceBarrier {
    use ActionBindRouteNode, ActorClassName, IsLastAction, Barrier;



    protected bool $isMiddleware = false;

    public function __toString(): string {
        return $this->render();
    }



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

    public function createUrl(Path|string|null $relative = null): Url {
        if (!isset($this->routeNode)) {
            throw new RuntimeException('Route Node is not set. Cannot create URL.');
        }

        return Router::createUrlFromNode($this->routeNode, $relative);
    }

    public function createBlockMiddleware(Privilege $privilege): Action {
        return new Block($this->getUserResource(), $privilege);
    }

    /**
     * Render the controller into default `PageView` given by `PageViewFactory`
     *
     * Override this method if implicit routing checks are required, otherwise override `perform()` method
     *
     * @param Request $request
     * @param Response $response
     * @return void
     *
     * @see PageView
     * @see PageViewFactory
     * @see Controller::perform()
     */
    public function performControllerAction(Request $request, Response $response): void {
        $response->render(
            PageViewFactory::getDefaultFactory()
                ->create()
                ->setComponent($this)
        );
    }

    /**
     * Checks routing node leaf run condition `isLastAction()` and checks read privilege on bound `UserResource`.
     * If both checks are met, `performControllerAction()` is called
     *
     * Override this method if you need to override the checks otherwise override `performControllerAction()` method
     *
     * @param Request $request
     * @param Response $response
     * @return void
     *
     * @see IsLastAction::isLastAction()
     * @see Controller::performControllerAction()
     */
    public function perform(Request $request, Response $response): void {
        if (!$this->isLastAction($request)) {
            return;
        }

        if (!$this->hasRequestAccess(Privilege::fromName(Privilege::READ), $request)) {
            $response->sendStatus(HttpCode::CE_FORBIDDEN);
        }

        $this->performControllerAction($request, $response);
    }
}