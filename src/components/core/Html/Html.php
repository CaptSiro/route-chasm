<?php

namespace components\core\Html;

use core\view\View;
use core\view\Renderer;

class Html implements View {
    use Renderer;

    public static function wrap(string $tag, string $content): string {
        return "<$tag>$content</$tag>";
    }

    public static function createLink(string $url, string $content): string {
        return "<a href='$url'>$content</a>";
    }

    public function __construct(
        protected readonly string $tag,
        protected array $attributes = [],
        protected null|string|View $content = null,
        protected readonly bool $doCloseTag = true
    ) {}
}