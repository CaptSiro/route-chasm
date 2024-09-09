<?php

namespace core\url;

use core\utils\Arrays;

class UrlBuilder {
    /** @var array<string, ?string> $params */
    protected array $params;

    /**
     * @param ?string $protocol
     * @param ?string $host
     * @param string $path
     * @param array<string, string> $query
     * @param array<string, string> $fragment
     */
    public function __construct(
        protected ?string $protocol = null,
        protected ?string $host = null,
        protected string $path = "",
        protected array $query = [],
        protected array $fragment = [],
    ) {
        $this->params = [];

        if (preg_match_all(Url::PARAM_REGEX, $this->path, $matches)) {
            $this->params = array_fill_keys(array_values($matches[1]), null);
        }
    }



    /**
     * @param string $protocol
     */
    public function setProtocol(string $protocol): void {
        $this->protocol = $protocol;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void {
        $this->host = $host;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function setParam(string $name, ?string $value = null): self {
        $this->params[$name] = $value;
        return $this;
    }

    public function setQuery(string $name, ?string $value = null, bool $doRemove = false): self {
        if ($doRemove) {
            if (isset($this->query[$name])) {
                unset($this->query[$name]);
            }

            return $this;
        }

        $this->query[$name] = $value;
        return $this;
    }

    public function setFragment(string $name, ?string $value = null, bool $doRemove = false): self {
        if ($doRemove) {
            if (isset($this->fragment[$name])) {
                unset($this->fragment[$name]);
            }

            return $this;
        }

        $this->fragment[$name] = $value;
        return $this;
    }

    public function build(): string {
        $path = $this->path;
        foreach ($this->params as $name => $value) {
            if (is_null($value)) {
                continue;
            }

            $path = Url::set($path, $name, $value);
        }

        $query = Arrays::urlEncode($this->query);
        if ($query !== '') {
            $query = '?' . $query;
        }

        $fragment = Arrays::urlEncode($this->fragment);
        if ($fragment !== '') {
            $fragment = '#' . $fragment;
        }

        if (!str_starts_with($this->path, '/')) {
            $this->path = '/'. $this->path;
        }

        $scheme = !(is_null($this->protocol) && is_null($this->host))
            ? $this->protocol .'://'. $this->host
            : '';

        return $scheme . $path . $query . $fragment;
    }

    public function __toString(): string {
        return $this->build();
    }

    public function clean(): self {
        $this->query = [];
        $this->fragment = [];
        $this->params = array_fill_keys(array_keys($this->params), null);
        return $this;
    }
}