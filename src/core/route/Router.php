<?php

namespace core\route;

use Closure;
use core\actions\Action;
use core\actions\Procedure;
use core\collections\dictionary\StrictStack;
use core\collections\graph\TreeVertex;
use core\communication\Request;
use core\communication\Response;

class Router {
    protected RouteTree $structure;



    public function __construct(?RouteTree $structure = null) {
        $this->structure = $structure ?? new RouteTree();
    }



    public function use(Route|string $route, Action|Closure ...$actions): static {
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
    protected function move(TreeVertex $destination): void {
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
    }

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
        $traces = $this->find($path);

        foreach ($traces as $trace) {
            /** @var StrictStack<?> $parameters */
            $parameters = $request->getParam();
            $first = true;

            $i = 0;
            foreach ($trace->getVertexes() as $vertex) {
                /** @var TreeVertex<RouteNode, RouteSegment> $vertex */

                if (!$first) {
                    $parent = $vertex->getParentEdge()?->get();
                    if (!is_null($parent)) {
                        $parent->match($path->getSegment($i), $parameters);
                    }
                }

                foreach ($vertex->get()->getActions() as $action) {
                    $action->perform($request, $response);
                }

                $first = false;
                $i++;
            }

            $parameters->clear();
        }
    }
}