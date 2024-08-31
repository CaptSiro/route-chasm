<?php

namespace core;

use core\dictionary\StrictMap;

class Url {
    public static function fromRequest(): self {
        $path = $_SERVER['REQUEST_URI'];
        $hostStart = strpos($path, $_SERVER['HTTP_HOST']);

        if ($hostStart !== false) {
            $path = substr($path, $hostStart + strlen($_SERVER['HTTP_HOST']));
        }

        $queryStart = strpos($path, "?");

        if ($queryStart !== false) {
            $path = substr($path, 0, $queryStart);
        }

        return new self(
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['HTTP_HOST'],
            $path,
            $_SERVER['QUERY_STRING'],
            new StrictMap($_GET)
        );
    }



    function __construct(
        private readonly string $protocol,
        private readonly string $host,
        private string $path,
        private readonly string $queryString,
        public readonly StrictMap $query
    ) {}



    public function full(): string {
        return $this->protocol ."://". $this->host . $this->path ."?". $this->queryString;
    }

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function setParam(string $param, string $value): void {
        $this->path = str_replace("[$param]", $value, $this->path);
    }

    public function hasParam(string $param): bool {
        return str_contains($this->path, "[$param]");
    }

    /**
     * @return string
     */
    public function getHost(): string {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        $app = App::getInstance();
        if ($app->options->get(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH)) {
            return substr($this->path, strlen($app->getHome()));
        }

        return $this->path;
    }

    /**
     * @return string
     */
    public function getQueryString(): string {
        return $this->queryString;
    }

    /**
     * @return string
     */
    public function getProtocol(): string {
        return $this->protocol;
    }
}