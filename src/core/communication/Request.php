<?php

namespace core\communication;

use core\App;
use core\dictionary\StrictDictionary;
use core\dictionary\StrictMap;
use core\dictionary\StrictStack;
use core\url\Url;

class Request {
    public const PARAM_ANY = "*";
    public const PARAM_ANY_TERMINATOR = "**";

    
    
    public static function test(?App $app = null, ?Url $url = null, ?string $httpMethod = "GET"): self {
        $format = new RequestFormat();
        $format->setFormatMatcher(new FormatMatcher());

        $ret = new self(
            $app ?? new App(),
            $format,
            $url ?? Url::fromRequest(),
            new StrictMap(),
            new StrictMap(),
            new StrictMap(),
            new StrictMap(),
        );

        $ret->httpMethod = $httpMethod;
        return $ret;
    }



    public string $httpMethod;

    private ?array $headers;

    public StrictDictionary|null $session;

    public StrictStack $param;

    readonly protected StrictMap $data;



    public function __construct(
        readonly protected App $app,
        readonly private LimitedFormat $format,
        readonly private Url $url,
        readonly private StrictDictionary $body,
        readonly private StrictDictionary $files,
        readonly private StrictDictionary $cookies,
        readonly private StrictDictionary $domain,
    ) {
        $this->httpMethod = $_SERVER["REQUEST_METHOD"];
        $this->headers = null;
        $this->param = new StrictStack();
        $this->data = new StrictMap();
    }



    public function getFormat(): string {
        return $this->format->getIdentifier($this);
    }

    public function getUrl(): Url {
        return $this->url;
    }

    public function getBody(): StrictDictionary {
        return $this->body;
    }

    public function getFiles(): StrictDictionary {
        return $this->files;
    }

    public function getDomain(): StrictDictionary {
        return $this->domain;
    }

    public function getHeaders(): ?array {
        return $this->headers;
    }

    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    public function getCookies(): StrictDictionary {
        return $this->cookies;
    }

    public function getParam(): StrictStack {
        return $this->param;
    }

    public function getSession(): ?StrictDictionary {
        return $this->session;
    }

    public function getHeader(string $name): ?string {
        if ($this->headers === null) {
            $this->headers = apache_request_headers();
        }

        return $this->headers[$name] ?? null;
    }

    public function setTestHeader(string $name, string $value): void {
        if ($this->headers === null) {
            $this->headers = apache_request_headers();
        }

        $this->headers[$name] = $value;
    }

    public function hasSession(): bool {
        return $this->session === null;
    }

    public function startSession(): void {
        session_start();
        $this->session = new StrictMap($_SESSION);
    }

    public function get(string $variable) {
        return $this->data->getStrict($variable);
    }

    public function set(string $name, mixed $value): void {
        $this->data->set($name, $value);
    }

    public function __debugInfo(): ?array {
        return [
            'httpMethod' => $this->httpMethod,
            'headers' => $this->headers,
            'url' => $this->url->full(),
            'body' => $this->body,
            'files' => $this->files,
            'cookies' => $this->cookies,
            'domain' => $this->domain
        ];
    }
}