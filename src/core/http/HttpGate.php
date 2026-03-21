<?php

namespace core\http;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\IsLastAction;
use core\communication\Request;
use core\communication\Response;
use core\patterns\AnyString;
use core\patterns\Pattern;
use core\route\RouteNode;

class HttpGate implements Action {
    use ActionBindRouteNode, IsLastAction;



    /**
     * @var array<Action> $actions
     */
    private array $actions;
    /**
     * @var array<Pattern> $queryGuards
     */
    private array $queryGuards;
    /**
     * @var array<Pattern> $bodyGuards
     */
    private array $bodyGuards;
    protected bool $isMiddleware;
    protected bool $checkIsLastAction;



    public function __construct(
        protected readonly string $httpMethod
    ) {
        $this->queryGuards = [];
        $this->bodyGuards = [];
        $this->isMiddleware = false;
        $this->checkIsLastAction = true;
    }



    public function setCheckIsLastAction(bool $checkIsLastAction): static {
        $this->checkIsLastAction = $checkIsLastAction;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    /**
     * @param array<Action> $actions
     * @return $this
     */
    public function setActions(array $actions): self {
        $this->actions = $actions;
        return $this;
    }

    public function middleware(): self {
        $this->isMiddleware = true;
        return $this;
    }

    public function query(string $name, ?Pattern $pattern = null): self {
        $this->queryGuards[$name] = $pattern ?? AnyString::getInstance();
        return $this;
    }

    public function body(string $name, Pattern $pattern): self {
        $this->bodyGuards[$name] = $pattern;
        return $this;
    }

    protected function checkGuards(Request $request): bool {
        foreach ($this->bodyGuards as $guard => $pattern) {
            if (!$pattern->match($request->getBody()->get($guard))) {
                return false;
            }
        }

        foreach ($this->queryGuards as $guard => $pattern) {
            if (!$pattern->match($request->getUrl()->getQuery()->get($guard))) {
                return false;
            }
        }

        return true;
    }

    protected function checkHttpMethod(string $httpMethod): bool {
        return $this->httpMethod === HttpMethod::ANY || strtoupper($httpMethod) === strtoupper($this->httpMethod);
    }



    // Action
    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        if ($this->checkIsLastAction && !$this->isLastAction($request)) {
            return;
        }

        if (!($this->checkHttpMethod($request->getHttpMethod()) && $this->checkGuards($request))) {
            return;
        }

        foreach ($this->actions as $endpoint) {
            $endpoint->perform($request, $response);
        }
    }

    public function getActorName(): string {
        return "HTTP ". $this->httpMethod;
    }
}