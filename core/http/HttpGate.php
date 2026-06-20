<?php

namespace core\http;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\IsLastAction;
use core\communication\body\DictionaryBody;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;
use core\utils\Regex;

class HttpGate implements Action {
    use ActionBindRouteNode, IsLastAction;



    /**
     * @var array<Action> $actions
     */
    private array $actions;
    /**
     * @var array<string> $queryGuards
     */
    private array $queryGuards;
    /**
     * @var array<string> $bodyGuards
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

    public function query(string $name, ?string $pattern = null): self {
        $this->queryGuards[$name] = $pattern ?? Regex::PATTERN_ANY;
        return $this;
    }

    public function body(string $name, string $pattern): self {
        $this->bodyGuards[$name] = $pattern;
        return $this;
    }

    protected function checkGuards(Request $request): bool {
        if (!empty($this->bodyGuards)) {
            if (is_null($body = $request->body(DictionaryBody::class, false))) {
                return false;
            }

            $fields = $body->getFields();

            foreach ($this->bodyGuards as $property => $pattern) {
                if (!preg_match($pattern, $body->get($property))) {
                    return false;
                }
            }
        }

        $query = $request->getUrl()->getQuery();

        foreach ($this->queryGuards as $property => $pattern) {
            if (!preg_match($pattern, $query->get($property))) {
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