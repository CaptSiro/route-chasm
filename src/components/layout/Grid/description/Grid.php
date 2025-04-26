<?php

namespace components\layout\Grid\description;

use Attribute;
use components\layout\Grid\Proxy\Proxy;
use components\layout\Grid\Proxy\TypeProxy;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Grid {
    public Proxy $proxy;

    public function __construct(
        ?Proxy $proxy = null
    ) {
        $this->proxy = $proxy ?? new TypeProxy();
    }
}