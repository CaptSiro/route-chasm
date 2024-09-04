<?php

namespace core;

use Closure;
use components\core\HttpError\HttpError;
use core\endpoints\Directory;
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

    public function expose(Path|string $path, Directory $directory): void {
        $parsed = Path::from($path);
        $this->use($parsed, Http::any(fn(Request $request, Response $response) => $directory->call($request, $response)));
        $this->use($parsed->merge("/**"), $directory);
    }

    public function resource(Path|string $path, Resource $resource): void {
        $parsed = $path instanceof Path
            ? $path
            : Parser::parse($path);

        $router = $resource->getRouter();
        $router->setNode($this->getLeaf($parsed));
    }

    function search(array $segments, int $current, MatchStack $stack, array &$out): void {
        $stack->push([], $this->getNode()->getEndpoints());
        $this->node->search($segments, $current, $stack, $out);
        $stack->pop();
    }

    /**
     * @param string $path
     * @return FoundNode[]
     */
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

        // normalize path
        $left = 0;
        $right = count($found) - 1;

        while ($left < $right) {
            if ($found[$left]->hasFlag(Segment::FLAG_ANY_TERMINATED)) {
                $tmp = $found[$right];
                $found[$right] = $found[$left];
                $found[$left] = $tmp;
                $right--;
            }

            $left++;
        }

        return $found;
    }

    public function isMiddleware(): bool {
        return false;
    }

    function call(Request $request, Response $response): void {
        $path = $this->findPath($request->url->getPath());
        foreach ($path as $found) {
            $request->param->push($found->matches);

            foreach ($found->endpoints as $endpoint) {
                $endpoint->call($request, $response);
            }

            $request->param->pop();
        }

        $response->render(new HttpError(
            "Called all responsible endpoints but none of them responded",
            Response::CODE_NOT_IMPLEMENTED
        ));
    }
}