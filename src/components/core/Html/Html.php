<?php

namespace components\core\Html;

use core\view\View;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Html implements View, Control {
    use Renderer, FormControl;

    public static function wrap(string $tag, string $content): string {
        return "<$tag>$content</$tag>";
    }

    public function __construct(
        protected readonly string $tag,
        protected array $attributes = [],
        protected null|string|View $content = null,
        protected readonly bool $doCloseTag = true
    ) {}
}