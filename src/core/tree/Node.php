<?php

namespace core\tree;

use core\endpoints\Endpoint;
use core\path\Segment;
use core\path\UrlPath;
use core\Render;
use core\Request;

class Node {
    protected ?Segment $segment;

    /** @var Node[] $children */
    protected array $children;
    protected int $insert;

    /** @var Endpoint[] $endpoints */
    protected array $endpoints;

    protected ?Node $parent;



    public function __construct() {
        $this->endpoints = [];
        $this->children = [];
        $this->parent = null;
        $this->segment = null;
        $this->insert = 0;
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

    /**
     * @return array<Node>
     */
    public function getChildren(): array {
        return $this->children;
    }

    public function addChild(Node $node): self {
        $node->parent = $this;

        $isAnyTerminated = $node->segment->hasFlag(Segment::FLAG_ANY_TERMINATED);
        if ($this->insert === count($this->children) && !$isAnyTerminated) {
            $this->children[] = $node;
        } else {
            array_splice($this->children, $this->insert, 0, [$node]);
        }

        $this->insert += intval(!$isAnyTerminated);

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

    public function toString(int $depth): string {
        $endpoints = array_map(fn($x) => $x instanceof Render ? basename(get_class($x)) : "$x", $this->endpoints);
        $string = str_repeat(' ', $depth) ."/$this->segment ". implode(', ', $endpoints) .'\n';

        foreach ($this->children as $child) {
            $string .= $child->toString($depth + 4);
        }

        return $string;
    }

    public function __toString(): string {
        return $this->toString(0);
    }
}