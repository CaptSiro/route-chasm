<?php

namespace core\communication;

use core\dictionary\Dictionary;
use core\Request;

interface Format {
    public const IDENT_TEXT = "text/plain";
    public const IDENT_XML = "application/xml";
    public const IDENT_JSON = "application/json";
    public const IDENT_HTML = "text/html";
    public const IDENT_FORM_URLENCODED = "application/x-www-form-urlencoded";
    public const IDENT_DEFAULT = self::IDENT_TEXT;



    public function setFormatMatcher(FormatMatcher $matcher): void;
    public function getTypeFromQuery(Dictionary $dictionary): ?string;
    public function getIdentifier(Request $request): string;
}