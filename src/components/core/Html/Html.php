<?php

namespace components\core\Html;

use core\utils\Arrays;
use core\view\View;
use core\view\Renderer;

class Html implements View {
    use Renderer;

    public static function escape(?string $content): string {
        if (is_null($content)) {
            return '';
        }

        return htmlspecialchars($content);
    }

    public static function escapeAttribute(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
    }

    public static function wrap(string $tag, string $content, array $attributes = []): string {
        return static::wrapUnsafe($tag, htmlspecialchars($content), $attributes);
    }

    public static function wrapUnsafe(string $tag, string $content, array $attributes = []): string {
        $attr = !empty($attributes)
            ? Arrays::htmlEncode($attributes)
            : '';

        return "<$tag $attr>$content</$tag>";
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