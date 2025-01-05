<?php

namespace core\communication;

trait BaseFormat {
    public function match(string $type): string {
        return match ($type) {
            't', 'text' => Format::IDENT_TEXT,
            'j', 'json' => Format::IDENT_JSON,
            'h', 'html' => Format::IDENT_HTML,
            'form' => Format::IDENT_FORM_URLENCODED,
            default => Format::IDENT_DEFAULT
        };
    }
}