<?php

namespace core\view;

use core\utils\Arrays;

class Html {
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

    public static function createLinkUnsafe(string $url, string $content, string $target = '_self'): string {
        return "<a href='$url' target='$target'>$content</a>";
    }
}