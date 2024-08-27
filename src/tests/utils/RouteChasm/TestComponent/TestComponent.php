<?php

namespace tests\utils\RouteChasm\TestComponent;

use core\Component;

class TestComponent extends Component {
    public function __construct(
        protected string $string
    ) {}
}