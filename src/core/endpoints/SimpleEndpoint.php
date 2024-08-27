<?php

namespace core\endpoints;

use core\App;
use core\path\Path;
use core\tree\Node;
use core\Url;

trait SimpleEndpoint {
    private Path $path;
    private ?Node $node = null;



    public function getUrl(): Url {
        $base = clone App::getInstance()->getRequest()->url;
        $base->setPath($this->getUrlPath());
        return $base;
    }

    // maybe to-do: Change to generic Node::getPathToSelf() -> map($n -> $n->getSegment()) -> str
    public function getUrlPath(): string {
        $current = $this->getNode();
        $segments = [];

        while (!is_null($current) && !is_null($current->getSegment())) {
            $segments[] = $current->getSegment();
            $current = $current->getParent();
        }

        return '/'. implode('/', array_reverse($segments));
    }

    public function setNode(Node $node): void {
        $this->node = $node;
    }

    public function getNode(): Node {
        return $this->node;
    }
}