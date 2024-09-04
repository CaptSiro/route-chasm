<?php

namespace core\tree;

use core\endpoints\Endpoint;
use core\path\Segment;
use core\path\UrlPath;
use core\Request;

class Node {
    private ?Segment $segment;
    /** @var Node[] $children */
    private array $children;
    /** @var Endpoint[] $endpoints */
    private array $endpoints;
    private ?Node $parent;



    public function __construct() {
        $this->endpoints = [];
        $this->children = [];
        $this->parent = null;
        $this->segment = null;
    }



    public function copy(Node $from, bool $doCopySegment): void {
        $this->endpoints = array_merge($this->endpoints, $from->endpoints);
        $start = count($this->children);
        $this->children = array_merge($this->children, $from->children);

        for ($i = $start; $i < count($this->children); $i++) {
            $this->children[$i]->parent = $this;
        }

        if ($doCopySegment) {
            $this->segment = $from->segment;
        }
    }

    public function getParent(): ?Node {
        return $this->parent;
    }

    public function getSegment(): ?Segment {
        return $this->segment;
    }

    public function setSegment(?Segment $segment): void {
        $this->segment = $segment;
    }

    /**
     * @return array<Endpoint>
     */
    public function getEndpoints(): array {
        return $this->endpoints;
    }

    public function search(UrlPath $path, SnapshotStack $stack): ?Trail {
        if ($this->segment?->hasFlag(Segment::FLAG_ANY_TERMINATED) || $path->isExhausted()) {
            $trail = $stack->merge()
                ->setFlag($this->segment?->getFlags() ?? 0);
            $params = $trail->getParams();

            if (isset($params[Request::PARAM_ANY_TERMINATOR])) {
                $steps = $path->getRemaining();

                if (!empty($steps)) {
                    $params[Request::PARAM_ANY_TERMINATOR] .= '/' .implode('/', $steps);
                }
            }

            return $trail;
        }

        $next = $path->next();

        foreach ($this->children as $node) {
            if (!$node->segment->test($path->current(), $matches)) {
                continue;
            }

            $stack->push($matches, $node->endpoints);

            if (!is_null($trail = $node->search($next, $stack))) {
                return $trail;
            }

            $stack->pop();
        }

        return null;
    }

    public function addChild(Node $node): self {
        $node->parent = $this;
        $this->children[] = $node;

        return $this;
    }

    public function findChild(Segment $segment): Node|null {
        foreach ($this->children as $node) {
            if (Segment::compare($node->getSegment(), $segment)) {
                return $node;
            }
        }

        return null;
    }

    public function addEndpoint(Endpoint $endpoint): self {
        $endpoint->setNode($this);
        $this->endpoints[] = $endpoint;

        return $this;
    }

    public function getPathToSelf(): string {
        $current = $this;
        $segments = [];

        while (!is_null($current) && !is_null($current->segment)) {
            $segments[] = $current->segment;
            $current = $current->parent;
        }

        return '/'. implode('/', array_reverse($segments));
    }
}