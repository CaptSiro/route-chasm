<?php

namespace core\actions;

use core\route\RouteNode;

trait BindRouteNode {
    protected ?RouteNode $node = null;

    public function getRouteNode(): ?RouteNode {
        return $this->node;
    }

    protected function bindRouteNode(RouteNode $node): void {
        $this->node = $node;
    }
}