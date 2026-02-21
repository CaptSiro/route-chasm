<?php

namespace core\actions;

use core\route\RouteNode;

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
}