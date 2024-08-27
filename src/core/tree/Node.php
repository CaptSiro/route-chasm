<?php

namespace core\tree;

use core\endpoints\Endpoint;
use core\path\Path;
use core\path\Segment;
use core\tree\traversable\FoundNode;
use core\tree\traversable\MatchStack;
use core\tree\traversable\Traversable;

class Node implements Traversable {
    private ?Segment $segment = null;
    /** @var Traversable[] $nodes */
    private array $nodes;
    /** @var Endpoint[] $endpoints */
    private array $endpoints;
    private ?Node $parent;



    public function __construct() {
        $this->endpoints = [];
        $this->nodes = [];
        $this->parent = null;
    }



    public function copy(Node $from, bool $doCopySegment): void {
        $this->endpoints = $from->endpoints;
        $this->nodes = $from->nodes;

        if ($doCopySegment) {
            $this->segment = $from->segment;
        }
    }

    public function setParent(?Node $parent): void {
        $this->parent = $parent;
    }

    public function getParent(): ?Node {
        return $this->parent;
    }

    public function setSegment(Segment $segment): void {
        $this->segment = $segment;
    }

    public function getSegment(): ?Segment {
        return $this->segment;
    }

    /**
     * @return array<Endpoint>
     */
    public function getEndpoints(): array {
        return $this->endpoints;
    }

    public function find(Segment $segment): Node|null {
        foreach ($this->nodes as $n) {
            if (Segment::compare($n->getNode()->getSegment(), $segment)) {
                return $n;
            }
        }

        return null;
    }

    public function search(array $segments, int $current, MatchStack $stack, array &$out): void {
        if (Segment::isLast($segments, $current)) {
            $stack->merge($params, $endpoints);
            $out[] = new FoundNode($params, $endpoints);
            return;
        }

        $next = Segment::next($segments, $current);

        foreach ($this->nodes as $n) {
            $hasPassed = $n->getNode()->getSegment()->test($segments[$current], $matches);

            if ($hasPassed) {
                $stack->push($matches, $n->getNode()->endpoints);
                $n->search($segments, $next, $stack, $out);
                $stack->pop();
            }
        }
    }

    public function create(Segment $segment): Node {
        $found = $this->find($segment);

        if ($found !== null) {
            return $found;
        }

        $new = new Node();

        $new->setSegment($segment);
        $new->setParent($this);
        $this->nodes[] = $new;

        return $new;
    }

    /**
     * @param array<Endpoint> $endpoints
     * @return void
     */
    public function assign(array $endpoints): void {
        foreach ($endpoints as $endpoint) {
            $endpoint->setNode($this);
        }

        $this->endpoints = array_merge($this->endpoints, $endpoints);
    }

    public function getNode(): Node {
        return $this;
    }

    public function walk(Path $path): ?Node {
        $path->rewind();
        $start = $this;

        while ($path->hasNext()) {
            $start = $start->find($path->next());

            if ($start === null) {
                return null;
            }
        }

        return $start;
    }
}