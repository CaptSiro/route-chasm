<?php

namespace components\layout\Grid\description;

use Attribute;
use components\layout\Grid\Loader\GridLoader;
use components\layout\Grid\Loader\StaticGridLoader;
use components\layout\Grid\Proxy\Proxy;
use components\layout\Grid\Proxy\TypeProxy;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class Grid {
    public readonly Proxy $proxy;

    public function __construct(
        ?Proxy $proxy = null,
        protected ?GridLoader $loader = null
    ) {
        $this->proxy = $proxy ?? new TypeProxy();
    }



    public function bindClass(ReflectionClass $reflection): void {
        if (!is_null($this->loader)) {
            return;
        }

        $class = $reflection->getName();
        if (!method_exists($class, "all")) {
            // todo
            // throw warning
            return;
        }

        $this->loader = new StaticGridLoader(
            fn() => call_user_func("$class::all")
        );
    }

    public function getLoader(): ?GridLoader {
        return $this->loader;
    }
}