<?php

namespace core\communication\format;

use core\collections\Dictionary;
use core\io\ContentType;

interface Format extends LimitedFormat {
    public const IDENT_TEXT = ContentType::TEXT;
    public const IDENT_XML = ContentType::XML;
    public const IDENT_JSON = ContentType::JSON;
    public const IDENT_HTML = ContentType::HTML;
    public const IDENT_FORM_URLENCODED = ContentType::FORM_URLENCODED;
    public const IDENT_DEFAULT = ContentType::TEXT;



    public function setFormatMatcher(FormatMatcher $matcher): self;

    public function getTypeFromQuery(Dictionary $dictionary): ?string;
}