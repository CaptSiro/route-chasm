<?php

namespace core;

use Closure;
use components\core\HttpError\HttpError;
use core\endpoints\Directory;
use core\endpoints\Endpoint;
use core\endpoints\Procedure;
use core\http\Http;
use core\http\HttpCode;
use core\path\Path;
use core\tree\Node;
use core\tree\SnapshotStack;
use core\tree\Trail;
use core\url\UrlPath;

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

    public function getInstanceId(): int {
        return $this->node->getInstanceId();
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

        foreach ($endpoints as $endpoint) {
            $leaf->addEndpoint($endpoint instanceof Endpoint
                ? $endpoint
                : new Procedure($endpoint));
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

    public function expose(Path|string $path, Directory $directory): void {
        $parsed = Path::from($path);
        $this->use($parsed, Http::any(fn(Request $request, Response $response) => $directory->execute($request, $response)));
        $this->use($parsed->merge("/**"), $directory);
    }

    public function resource(Path|string $path, Resource $resource): void {
        $this->bind($path, $resource->getRouter());
    }

    public function findPath(string $path): ?Trail {
        $snapshots = new SnapshotStack();

        $snapshots->push([], $this->node->getEndpoints());
        return $this->node->search(UrlPath::from($path), $snapshots);
    }

    public function getUrlPath(): string {
        return $this->node->getPathToSelf();
    }

    public function isMiddleware(): bool {
        return false;
    }

    public function execute(Request $request, Response $response): void {
        $trail = $this->findPath($request->url->getPath());
        if (is_null($trail)) {
            $response->render(new HttpError(
                "Resource not found",
                HttpCode::CE_NOT_FOUND
            ));
            return;
        }

        $request->param->push($trail->getParams());

        foreach ($trail->getEndpoints() as $endpoint) {
            $endpoint->execute($request, $response);
        }

        $request->param->pop();

        $response->render(new HttpError(
            "Called all responsible endpoints but none of them responded",
            HttpCode::SE_NOT_IMPLEMENTED
        ));
    }

    public function map(): string {
        return "<pre>". strtr("$this->node", ['\n' => '<br>']) ."</pre>";
    }
}