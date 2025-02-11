<?php

namespace components\core\Html;

use core\view\Render;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Html implements Render, Control {
    use Renderer, FormControl;

    public function __construct(
        protected readonly string $tag,
        protected array $attributes = [],
        protected null|string|Render $content = null,
        protected readonly bool $doCloseTag = true
    ) {}
}