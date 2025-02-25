<?php

namespace core;

use Closure;
use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\Procedure;
use core\http\HttpCode;
use core\path\Path;
use core\path\SearchPath;
use core\tree\Node;
use core\tree\SnapshotStack;
use core\tree\Trail;

class Router {
    protected Node $node;



    public function __construct() {
        $this->node = new Node();
    }



    /**
     * @return array<Endpoint>
     */
    public function getEndpoints(): array {
        return $this->node->getEndpoints();
    }

    public function setNode(Node $node, Node $parent): void {
        $node->copy($this->node, false);
        $this->node = $node;
        $this->node->setParent($parent);
    }

    protected function getLeaf(Path $path): Node {
        $node = $this->node;

        $path->rewind();
        while (!$path->isExhausted()) {
            $segment = $path->current();
            $n = $node->findChild($segment);

            if (is_null($n)) {
                $n = new Node();
                $n->setSegment($segment);
                $node->addChild($n);
            }

            $node = $n;
            $path->next();
        }

        return $node;
    }

    public function use(Path|string $path, Endpoint|Closure ...$endpoints): void {
        $parsed = Path::from($path);
        $leaf = $this->getLeaf($parsed);

        $router = new Router();
        $router->setNode(
            $leaf,
            $parsed->getDepth() === 0
                ? $this->node
                : $leaf->getParent()
        );

        foreach ($endpoints as $item) {
            $endpoint = $item instanceof Endpoint
                ? $item
                : new Procedure($item);

            $leaf->addEndpoint($endpoint);
            $endpoint->onContextBind($router);
        }
    }

    public function bind(Path|string $path, Router $router): void {
        $parsed = Path::from($path);
        $leaf = $this->getLeaf($parsed);

        $router->setNode($leaf, $parsed->getDepth() === 0
            ? $this->node
            : $leaf->getParent()
        );
    }

    public function expose(Path|string $path, Endpoint $endpoint): void {
        $parsed = Path::from($path);
        $this->use($parsed, $endpoint);
        $this->use($parsed->merge("/**"), $endpoint);
    }

    public function resource(Path|string $path, Resource $resource): void {
        $this->bind($path, $resource->getRouter());
    }

    public function findPath(string $path): ?Trail {
        $snapshots = new SnapshotStack();

        $snapshots->push([], $this->node->getEndpoints());
        return $this->node->search(SearchPath::from($path), $snapshots);
    }

    /**
     * Home is not automatically prepended
     *
     * @see App::prependHome
     * @return string
     */
    public function getUrlPath(): string {
        return $this->node->getPathToSelf();
    }

    public function isMiddleware(): bool {
        return false;
    }

    public function execute(Request $request, Response $response): void {
        $trail = $this->findPath($request->getUrl()->getPath());
        if (is_null($trail)) {
            $response->error(
                "Resource not found",
                HttpCode::CE_NOT_FOUND
            );
            return;
        }

        $request->getParam()->push($trail->getParams());
        $method = $request->getUrl()->getQuery()->get('x');

        foreach (array_reverse($trail->getEndpoints()) as $endpoint) {
            if (!is_null($method) && $method !== '' && method_exists($endpoint, $method)) {
                call_user_func_array([$endpoint, $method], [$request, $response]);
                continue;
            }

            $endpoint->execute($request, $response);
        }

        $request->getParam()->pop();

        $response->error(
            "Called all responsible endpoints but none of them responded",
            HttpCode::SE_NOT_IMPLEMENTED
        );
    }

    public function map(): string {
        return "<pre>". strtr("$this->node", ['\n' => '<br>']) ."</pre>";
    }
}