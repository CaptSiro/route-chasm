<?php

namespace core\endpoints;

use core\App;
use core\path\Segment;
use core\Router;
use core\tree\Node;
use core\url\Url;

trait SimpleEndpoint {
    private ?Node $node = null;



    public function getUrl(): Url {
        $base = clone App::getInstance()->getRequest()->getUrl();
        $base->setPath($this->getUrlPath());
        return $base;
    }

    // maybe to-do: Change to generic Node::getPathToSelf() -> map($n -> $n->getSegment()) -> str
    public function getUrlPath(): string {
        $current = $this->getNode();
        $segments = [];

        while (!is_null($current) && !is_null($current->getSegment())) {
            if (!$current->getSegment()->hasFlag(Segment::FLAG_ANY_TERMINATED)) {
                $segments[] = $current->getSegment();
            }
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

    public function getEndpointLabel(): string {
        return basename(get_class($this));
    }

    public function onContextBind(Router $leaf): void {}
}