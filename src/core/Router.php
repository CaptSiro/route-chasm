<?php

namespace core;

use Closure;
use core\dictionary\StrictMap;
use core\endpoints\Endpoint;
use core\endpoints\Procedure;
use core\endpoints\SimpleEndpoint;
use core\path\parser\Parser;
use core\path\Path;
use core\path\Segment;
use core\tree\Node;
use core\tree\traversable\FoundNode;
use core\tree\traversable\MatchStack;
use core\tree\traversable\Traversable;

class Router implements Traversable, Endpoint {
    use SimpleEndpoint;



    public function __construct() {
        $this->node = new Node();
    }

    /**
     * @return Node
     */
    public function getNode(): Node {
        return $this->node;
    }

    /**
     * @param Node $node
     */
    public function setNode(Node $node): void {
        $node->copy($this->node, false);
        $this->node = $node;
    }

    public function getLeaf(Path $path): Node {
        $node = $this->node;
        while ($path->hasNext()) {
            $node = $node->create($path->next());
        }

        return $node;
    }

    function use(Path|string $path, Endpoint|Closure ...$endpoints): void {
        $parsed = Path::from($path);
        $leaf = $this->getLeaf($parsed);

        $e = [];
        foreach ($endpoints as $x) {
            if ($x instanceof Router) {
                $x->setNode($leaf);
                continue;
            }

            $e[] = $x instanceof Endpoint
                ? $x
                : new Procedure($x);
        }

        $leaf->assign($e);
    }

    public function resource(Path|string $path, Resource $resource): void {
        $parsed = $path instanceof Path
            ? $path
            : Parser::parse($path);

        $router = $resource->getRouter();
        $router->setNode($this->getLeaf($parsed));
    }

    function search(array $segments, int $current, MatchStack $stack, array &$out): void {
        $this->node->search($segments, $current, $stack, $out);
    }

    public function findPath(string $path): array {
        $segments = explode('/', $path);
        /** @var array<FoundNode> $found */
        $found = [];

        $this->search(
            $segments,
            Segment::next($segments, Segment::FIRST),
            new MatchStack(),
            $found
        );

        return $found;
    }

    function call(Request $request, Response $response): void {
        foreach ($this->findPath($request->url->getPath()) as $found) {
            $request->param->push($found->matches);

            foreach ($found->endpoints as $endpoint) {
                $endpoint->call($request, $response);
            }

            $request->param->pop();
        }
    }
}