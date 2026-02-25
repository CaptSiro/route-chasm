<?php

namespace components\core\MarkDown;

use core\view\Renderer;
use core\view\View;

class MarkDown implements View {
    use Renderer;



    public function __construct(
        protected string $markDown
    ) {}
}