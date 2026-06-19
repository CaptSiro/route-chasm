<?php

namespace core\communication;

class FormatMatcher {
    public function matchQuery(string $type): string {
        return match ($type) {
            't', 'text' => Format::IDENT_TEXT,
            'j', 'json' => Format::IDENT_JSON,
            'h', 'html' => Format::IDENT_HTML,
            'x', 'xml' => Format::IDENT_XML,
            'f', 'form' => Format::IDENT_FORM_URLENCODED,
            default => Format::IDENT_DEFAULT
        };
    }

    public function matchContentType(string $type): string {
        return match ($type) {
            'text/plain' => Format::IDENT_TEXT,
            'text/html' => Format::IDENT_HTML,
            'application/json' => Format::IDENT_JSON,
            'multipart/form-data', 'application/x-www-form-urlencoded' => Format::IDENT_FORM_URLENCODED,
            'text/xml', 'application/xml' => Format::IDENT_XML,
            default => Format::IDENT_DEFAULT
        };
    }
}