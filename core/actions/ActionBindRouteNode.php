<?php

namespace core\actions;

use core\App;
use core\route\Path;
use core\route\RouteNode;
use core\url\Url;

trait ActionBindRouteNode {
    protected ?RouteNode $routeNode = null;

    public function getRouteNode(): ?RouteNode {
        return $this->routeNode;
    }

    protected function bindRouteNode(RouteNode $node): void {
        $this->routeNode = $node;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function createUrl(?Path $relative = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->routeNode->getRoute()->toStaticPath();

        if (!is_null($relative)) {
            foreach ($relative as $segment) {
                $path->append($segment);
            }
        }

        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        return $ret;
    }
}