<?php

namespace components\layout\Grid\description;

use Attribute;
use components\layout\Grid\Loader\GridLoader;
use components\layout\Grid\Loader\StaticGridLoader;
use components\layout\Grid\Proxy\Proxy;
use ReflectionClass;
use RuntimeException;

#[Attribute(Attribute::TARGET_CLASS)]
class Grid {
    public function __construct(
        protected ?string $namespace = null,
        protected ?Proxy $proxy = null,
        protected ?GridLoader $loader = null
    ) {}



    public function getNamespace(): ?string {
        return $this->namespace;
    }

    public function getLoader(): ?GridLoader {
        return $this->loader;
    }

    public function getProxy(): ?Proxy {
        return $this->proxy;
    }

    public function bindClass(ReflectionClass $reflection): void {
        if (!is_null($this->loader)) {
            return;
        }

        $class = $reflection->getName();
        if (!method_exists($class, "all")) {
            throw new RuntimeException("Cannot create Grid from class '$class' that does not have 'all' static method");
        }

        $this->loader = new StaticGridLoader(
            fn() => call_user_func("$class::all")
        );
    }
}