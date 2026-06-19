<?php

namespace core\url;

use core\collections\Dictionary;
use core\collections\dictionary\StrictMap;
use core\collections\StrictDictionary;
use core\Copy;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\utils\Arrays;
use core\utils\Strings;
use JsonSerializable;
use RuntimeException;

class Url implements Copy, JsonSerializable {
    public const SEPARATOR_PROTOCOL = '://';
    public const SEPARATOR_PATH = '/';
    public const REGEX_URL = "/^(([^:\/?#]+):)?(\/\/(([^:\/?#\n]*):?([0-9]*)))?([^?#\n]*)(\?([^#\n]*))?(#([^\n]*))?/";



    public static function from(string $literal): static {
        $valid = preg_match(self::REGEX_URL, $literal, $matches);
        if (!$valid) {
            throw new RuntimeException("Not a valid URL literal: $literal");
        }

        $protocol = Strings::nonEmpty($matches[2], 'http');
        $port = intval(Strings::nonEmpty($matches[6], '-1'));

        return (new static())
            ->setProtocol($protocol)
            ->setHost(Strings::nonEmpty($matches[5], 'localhost'))
            ->setPort($port)
            ->setPath(Path::from(Strings::nonEmpty($matches[7], '')))
            ->setQuery(new StrictMap(
                Strings::parseUrlEncoded(Strings::nonEmpty($matches[9] ?? null, '')))
            );
    }

    public static function fromRequest(): static {
        $path = $_SERVER['REQUEST_URI'];
        $hostStart = strpos($path, $_SERVER['HTTP_HOST']);

        if ($hostStart !== false) {
            $path = substr($path, $hostStart + strlen($_SERVER['HTTP_HOST']));
        }

        $queryStart = strpos($path, "?");

        if ($queryStart !== false) {
            $path = substr($path, 0, $queryStart);
        }

        return (new static())
            ->setProtocol($_SERVER['REQUEST_SCHEME'] ?? "http")
            ->setHost($_SERVER['HTTP_HOST'] ?? "localhost")
            ->setPath(Path::from($path))
            ->setQuery(new StrictMap($_GET));
    }




    protected string $protocol;
    protected string $host;
    protected int $port;
    protected Path $path;
    protected StrictDictionary $query;


    public function __construct() {
        $this->protocol = 'http';
        $this->host = 'localhost';
        $this->port = -1;
        $this->path = new Path([]);
        $this->query = new StrictMap();
    }

    public function __toString(): string {
        return $this->toString();
    }



    public function getProtocol(): string {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): static {
        $this->protocol = $protocol;
        return $this;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function setHost(string $host): static {
        $this->host = $host;
        return $this;
    }

    public function getPort(): int {
        return $this->port;
    }

    public function setPort(int $port): static {
        $this->port = $port;
        return $this;
    }

    public function getPath(): Path {
        return $this->path;
    }

    public function setPath(Path $path): static {
        $this->path = $path;
        return $this;
    }

    /**
     * @return StrictDictionary<string>
     */
    public function getQuery(): StrictDictionary {
        return $this->query;
    }

    public function loadTransitiveQueries(Dictionary $query): static {
        foreach (RouteChasmEnvironment::TRANSITIVE_QUERIES as $transitive) {
            if (!is_null($value = $query->get($transitive))) {
                $this->setQueryArgument($transitive, $value);
            }
        }

        return $this;
    }

    public function getQueryString(): string {
        $queryArray = $this->query->toArray();
        if (empty($queryArray)) {
            return '';
        }

        return Arrays::urlEncode($queryArray);
    }

    public function setQuery(StrictDictionary $query): static {
        $this->query = $query;
        return $this;
    }

    public function setQueryArgument(string $name, string $value = ''): static {
        $this->query->set($name, $value);
        return $this;
    }

    public function toString(): string {
        $port = $this->port < 0
            ? ''
            : ':'. $this->port;

        $url = $this->protocol .'://'. $this->host . $port . $this->path;

        $queryArray = $this->query->toArray();
        if (empty($queryArray)) {
            return $url;
        }

        return $url .'?'. Arrays::urlEncode($queryArray);
    }



    // Copy
    public function copy(): static {
        $instance = new static();

        $instance
            ->setProtocol($this->protocol)
            ->setHost($this->host)
            ->setPort($this->port)
            ->setPath(Path::from($this->path->toString()))
            ->setQuery(new StrictMap([...$this->query->toArray()]));

        return $instance;
    }



    // JsonSerializable
    public function jsonSerialize(): string {
        return $this->toString();
    }
}