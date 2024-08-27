<?php

namespace core;

use core\dictionary\StrictDictionary;
use core\dictionary\StrictMap;
use core\dictionary\StrictStack;

class Request {
    public static function test(?App $app = null, ?Url $url = null, ?string $httpMethod = "GET"): self {
        $ret = new self(
            $app ?? new App(),
            $url ?? Url::fromRequest(),
            new StrictMap(App::notDefinedCallback(), []),
            new StrictMap(App::notDefinedCallback(), []),
            new StrictMap(App::notDefinedCallback(), []),
            new StrictMap(App::notDefinedCallback(), []),
        );

        $ret->httpMethod = $httpMethod;
        return $ret;
    }



    public string $httpMethod;

    private array $headers;

    public StrictDictionary|null $session;

    public StrictStack $param;

    readonly protected StrictMap $data;



    public function __construct(
        readonly protected App $app,
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



    public function getHeader(string $name): ?string {
        return $this->headers[$name] ?? null;
    }

    public function setTestHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
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

    public function getResponseType(): string {
        $matcher = $this->app->getResponseTypeMatcher();

        $header = $this->getHeader(Http::HEADER_X_RESPONSE_TYPE);
        if (!is_null($header)) {
            return $matcher($header);
        }

        $query = $this->url->query->get("t") ?? $this->url->query->get("type");
        if (!is_null($query)) {
            return $matcher($query);
        }

        if ($this->httpMethod === "GET" && App::getInstance()->options->get(App::OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET)) {
            return 'HTML';
        }

        return 'TEXT';
    }
}