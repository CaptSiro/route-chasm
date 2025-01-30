<?php

namespace core\http;

use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use core\path\Path;
use core\patterns\AnyString;
use core\patterns\Pattern;

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
        protected readonly string $httpMethod
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
        return $this->httpMethod === HttpMethod::ANY || $httpMethod === $this->httpMethod;
    }

    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    public function execute(Request $request, Response $response): void {
        if (Path::depth($request->getUrl()->getPath()) !== Path::depth($this->getUrlPath()) && !$this->isMiddleware) {
            return;
        }

        if (!($this->checkHttpMethod($request->httpMethod) && $this->checkGuards($request))) {
            return;
        }

        foreach ($this->endpoints as $endpoint) {
            $endpoint->execute($request, $response);
        }
    }

    public function __toString(): string {
        return "HTTP ". $this->httpMethod;
    }
}