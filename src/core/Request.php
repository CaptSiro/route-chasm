<?php

namespace core;

use core\dictionary\StrictDictionary;
use core\dictionary\StrictMap;
use core\dictionary\StrictStack;

class Request {
    public static function test(?Url $url = null): self {
        return new self(
            $url ?? Url::fromRequest(),
            new StrictMap(App::notDefinedCallback(), []),
            new StrictMap(App::notDefinedCallback(), []),
            new StrictMap(App::notDefinedCallback(), []),
            new StrictMap(App::notDefinedCallback(), []),
        );
    }



    readonly public string $httpMethod;

    readonly private array $headers;

    public StrictDictionary|null $session;

    public StrictStack $param;

    readonly protected StrictMap $data;



    public function __construct(
        readonly public Url $url,
        readonly public StrictDictionary $domain,
        readonly public StrictDictionary $files,
        readonly public StrictDictionary $body,
        readonly public StrictDictionary $cookies,
    ) {
        $this->httpMethod = $_SERVER["REQUEST_METHOD"];
        $this->headers = apache_request_headers();
        $this->param = new StrictStack(App::notDefinedCallback());
        $this->data = new StrictMap(App::notDefinedCallback(), []);
    }



    public function getHeader(string $name) {
        return $this->headers[$name];
    }

    public function hasSession(): bool {
        return $this->session === null;
    }

    public function startSession(): void {
        session_start();
        $this->session = new StrictMap(App::notDefinedCallback(), $_SESSION);
    }

    public function get(string $variable) {
        return $this->data->getStrict($variable);
    }

    public function set(string $name, mixed $value): void {
        $this->data->set($name, $value);
    }
}