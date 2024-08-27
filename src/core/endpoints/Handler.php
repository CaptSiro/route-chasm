<?php

namespace core\endpoints;

use core\Request;
use patterns\Pattern;

class Handler implements Endpoint {
    use SimpleEndpoint;



    /**
     * @var array<Endpoint> $handles
     */
    private array $handles;
    /**
     * @var array<Pattern> $queryGuards
     */
    private array $queryGuards;
    /**
     * @var array<Pattern> $bodyGuards
     */
    private array $bodyGuards;



    public function __construct(
        private readonly string $httpMethod
    ) {
        $this->queryGuards = [];
        $this->bodyGuards = [];
    }



    /**
     * @return string
     */
    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    public function setHandles(array $handles): self {
        $this->handles = $handles;
        return $this;
    }

    public function query(string $name, Pattern $pattern): self {
        $this->queryGuards[$name] = $pattern;
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

    public function call(Request $request): void {
        if ($request->httpMethod !== $this->httpMethod || !$this->checkGuards($request)) {
            return;
        }

        foreach ($this->handles as $handle) {
            $handle->call($request);
        }
    }
}