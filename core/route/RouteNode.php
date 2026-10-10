<?php

namespace core\route;

use Closure;
use core\actions\Action;
use core\collections\graph\TreeVertex;
use core\Flags;

class RouteNode {
    use Flags;

    public const FLAG_IS_TERMINAL = 1;



    /**
     * @return TreeVertex<RouteNode, ?>
     */
    public static function createEmpty(): TreeVertex {
        $node = new static();
        $vertex = new TreeVertex($node);
        $node->vertex = $vertex;
        return $vertex;
    }



    protected ?TreeVertex $vertex;

    protected ?Closure $loader = null;



    /**
     * @param array<Action> $actions
     */
    public function __construct(
        protected array $actions = []
    ) {}

    public function __toString(): string {
        $current = $this;
        $route = '';

        while (!is_null($current)) {
            $edge = $current->vertex?->getParentEdge();
            if (is_null($edge)) {
                break;
            }

            $route = $edge->get() . $route;
            $current = $edge->getVertex();
        }

        return '/'. $route;
    }



    /**
     * @return TreeVertex<RouteNode, ?>|null
     */
    public function getVertex(): ?TreeVertex {
        return $this->vertex;
    }

    /**
     * @param TreeVertex<RouteNode, ?> $vertex
     */
    public function setVertex(TreeVertex $vertex): void {
        $this->vertex = $vertex;
    }

    /**
     * Registers a callback that adds this node's sub-routes on demand. It runs once, the first time
     * route search reaches this node (RouteTree::find) or when load() is called explicitly.
     */
    public function setLoader(?Closure $loader): void {
        $this->loader = $loader;
    }

    public function load(): void {
        if (is_null($loader = $this->loader)) {
            return;
        }

        $this->loader = null;
        $loader();
    }

    /**
     * @return array<Action>
     */
    public function getActions(): array {
        return $this->actions;
    }

    public function addAction(Action $action): void {
        $this->actions[] = $action;
        $action->onBind($this);
    }

    public function getParent(): ?self {
        return $this->vertex
            ?->getParentVertex()
            ?->get();
    }

    public function getSegment(): ?RouteSegment {
        return $this->vertex
            ?->getParentEdge()
            ?->get();
    }

    public function getRouter(): ?Router {
        if (is_null($this->vertex)) {
            return null;
        }

        return new Router(new RouteTree($this->vertex));
    }

    public function getRoute(): Route {
        return Route::fromSegments(RouteTree::getRouteSegments($this->vertex));
    }
}