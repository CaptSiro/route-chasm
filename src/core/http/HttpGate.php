<?php

namespace core\http;

use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use core\path\Path;
use core\Request;
use core\Response;
use patterns\AnyString;
use patterns\Pattern;

class HttpGate implements Endpoint {
    use SimpleEndpoint;



    /**
     * @var array<Endpoint> $endpoints
     */
    private array $endpoints;
    /**
     * @var array<Pattern> $queryGuards
     */
    private array $queryGuards;
    /**
     * @var array<Pattern> $bodyGuards
     */
    private array $bodyGuards;
    protected bool $isMiddleware;



    public function __construct(
        private readonly string $httpMethod
    ) {
        $this->queryGuards = [];
        $this->bodyGuards = [];
        $this->isMiddleware = false;
    }



    /**
     * @return string
     */
    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    public function setEndpoints(array $endpoints): self {
        $this->endpoints = $endpoints;
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
            if (!$pattern->match($request->body->get($guard))) {
                return false;
            }
        }

        foreach ($this->queryGuards as $guard => $pattern) {
            if (!$pattern->match($request->url->query->get($guard))) {
                return false;
            }
        }

        return true;
    }

    protected function checkHttpMethod(string $httpMethod): bool {
        return $this->httpMethod === HttpMethod::ANY || $httpMethod === $this->httpMethod;
    }

    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function execute(Request $request, Response $response): void {
        if (Path::depth($request->url->getPath()) !== Path::depth($this->getUrlPath()) && !$this->isMiddleware) {
            return;
        }

        if (!($this->checkHttpMethod($request->httpMethod) && $this->checkGuards($request))) {
            return;
        }

        foreach ($this->endpoints as $endpoint) {
            $endpoint->execute($request, $response);
        }
    }
}