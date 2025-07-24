<?php

namespace tests\utils\RouteChasm\TestComponent;

use core\view\Component;

class TestComponent extends Component {
    public function __construct(
        protected string $string
    ) {
        parent::__construct();
    }
}