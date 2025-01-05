<?php

namespace core\communication;

use core\dictionary\Dictionary;
use core\Request;

class ResponseFormat implements Format {
    use BaseFormat;

    public const QUERY_PARAMETER = "p";
    public const QUERY_PARAMETER_LONG = "response-format";



    public function getTypeFromQuery(Dictionary $dictionary): ?string {
        return $dictionary->get(self::QUERY_PARAMETER)
            ?? $dictionary->get(self::QUERY_PARAMETER_LONG);
    }

    public function getIdentifier(Request $request): string {

    }
}