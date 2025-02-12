<?php

namespace core\url;

use core\App;
use core\collection\StrictDictionary;
use core\collection\StrictMap;
use core\utils\Strings;

class Url {
    public const PARAM_REGEX = "/\[([^\]]+)\]/";
    public const SEPARATOR_PROTOCOL = '://';
    public const SEPARATOR_PATH = '/';



    public static function set(string $url, string $parameter, string $value): string {
        return str_replace("[$parameter]", $value, $url);
    }

    public static function parseQuery(string $literal): StrictDictionary {
        $map = new StrictMap();

        foreach (explode('&', $literal) as $name => $value) {
            $map->set($name, $value);
        }

        return $map;
    }

    public static function from(string $fullyQualifiedUrl): Url {
        $protocol = Strings::split($fullyQualifiedUrl, self::SEPARATOR_PROTOCOL, $rest) ?? 'http';
        $host = Strings::split($rest, '/', $rest)
            ?? App::getInstance()
                ->getRequest()
                ->getUrl()
                ->host;
        $path = Strings::split($rest, '?', $query);
        $queryDictionary = self::parseQuery($query);


        return new Url(
            $protocol,
            $host,
            '/'. $path,
            $query,
            $queryDictionary
        );
    }

    public static function relative(
        string $path,
        ?string $protocol = null,
        ?string $host = null,
        ?string $query = null
    ): Url {
        $request = App::getInstance()
            ->getRequest();

        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            $path = $request->getUrl()->getPath() .'/'. $path;
        }

        return new Url(
            $protocol ?? $request->getUrl()->protocol,
            $host ?? $request->getUrl()->host,
            $path,
            $query ?? '',
            $query === null
                ? new StrictMap()
                : self::parseQuery($query)
        );
    }

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
            $_SERVER['REQUEST_SCHEME'] ?? "http",
            $_SERVER['HTTP_HOST'] ?? "localhost",
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
        private readonly StrictDictionary $query
    ) {}



    public function getQuery(): StrictDictionary {
        return $this->query;
    }

    public function full(): string {
        $query = $this->queryString === ''
            ? ''
            : '?' . $this->queryString;

        return $this->protocol ."://". $this->host . $this->path . $query;
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

    public function getHost(): string {
        return $this->host;
    }

    /**
     * If option for removing home from is set it will perform such action
     * @see App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH
     * @return string
     */
    public function getPath(): string {
        $app = App::getInstance();
        if ($app->getOptions()->get(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH)) {
            return substr($this->path, strlen($app->getHome()));
        }

        return $this->path;
    }

    /**
     * Ignores option for home removal
     * @see App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH
     * @return string
     */
    public function getRealPath(): string {
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

    public function __toString(): string {
        return $this->full();
    }
}