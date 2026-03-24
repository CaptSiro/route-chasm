<?php

namespace core\route;

use Closure;
use core\actions\Action;
use core\actions\Procedure;
use core\App;
use core\collections\dictionary\StrictStack;
use core\collections\graph\TreeVertex;
use core\communication\Request;
use core\communication\Response;
use core\RouteChasmEnvironment;
use core\url\Url;

class Router {
    protected RouteTree $structure;



    public function __construct(
        ?RouteTree $structure = null
    ) {
        $this->structure = $structure ?? new RouteTree();
    }



    public function getStructure(): RouteTree {
        return $this->structure;
    }

    public function get(Route|string $route): Router {
        return new static(
            $this->structure->getSubTree(Route::resolve($route))
        );
    }

    public function use(Route|string $route, Closure|Action ...$actions): static {
        $node = $this->structure->getNode(Route::resolve($route));

        foreach (Procedure::resolve($actions) as $action) {
            $node->addAction($action);
        }

        return $this;
    }

    /**
     * @param TreeVertex<RouteNode, ?> $destination
     * @return void
     */
    private function move(TreeVertex $destination): void {
        $root = $this->structure->getRoot();
        foreach ($root->getEdges() as $edge) {
            // TreeVertex implements setting parent Edge in addEdge(Edge)
            $destination->addEdge($edge);
        }

        $destinationNode = $destination->get();
        foreach ($root->get()->getActions() as $action) {
            $destinationNode->addAction($action);
        }

        $this->structure->setRoot($destination);
        $this->onBind($destinationNode);
    }

    protected function onBind(RouteNode $bindingPoint): void {}

    public function bind(Route|string $route, Router $router): static {
        $vertex = $this->structure->getVertex(Route::resolve($route));
        $router->move($vertex);
        return $this;
    }

    public function expose(Route|string $route, Action|Closure $action): static {
        $route = Route::resolve($route);
        $routeExtended = $route->copy()->extend(Route::from("/**"));

        return $this
            ->use($route, $action)
            ->use($routeExtended, $action);
    }

    /**
     * @param Path $path
     * @return array<Trace<RouteNode, RouteSegment>>
     */
    public function find(Path $path): array {
        return $this->structure->traceSearch($path);
    }

    public function performActions(Path $path, Request $request, Response $response): void {
        /** @var StrictStack<?> $parameters */
        $parameters = $request->getParam();
        $traces = $this->find($path);

        $method = $request
            ->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_EXECUTE);

        foreach ($traces as $trace) {
            $index = -1;
            $pathIndex = $path->getOffset();
            $pop = 0;
            $vertexes = $trace->getVertexes();
            $last = array_key_last($vertexes);

            foreach ($vertexes as $key => $vertex) {
                /** @var TreeVertex<RouteNode, RouteSegment> $vertex */

                $request->set(Request::PATH_INDEX, $pathIndex);
                if (!is_null($segment = $vertex->getParentEdge()?->get())) {
                    if ($segment->match($path->getSegment($index), $parameters)) {
                        $pop++;
                    }
                }

                foreach ($vertex->get()->getActions() as $action) {
                    if (!$action->isMiddleware() && $last !== $key) {
                        continue;
                    }

                    if (!is_null($method) && $method !== '' && method_exists($action, $method)) {
                        call_user_func_array([$action, $method], [$request, $response]);
                        continue;
                    }

                    $action->perform($request, $response);
                }

                $pathIndex++;
                $index++;
            }

            for ($j = 0; $j < $pop; $j++) {
                $parameters->pop();
            }
        }
    }

    public function getRoute(): Route {
        return $this->structure->getRoute();
    }

    public function isBound(): bool {
        return !is_null($this->structure->getRoot()->getParentEdge());
    }

    public function createUrl(?Path $relative = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath();

        if (!is_null($relative)) {
            foreach ($relative as $segment) {
                $path->append($segment);
            }
        }

        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        return $ret;
    }
}