<?php

namespace core\communication;

use core\App;
use core\collection\Dictionary;
use core\http\HttpHeader;

class ResponseFormat implements Format {
    use BaseFormat;

    public const QUERY_PARAMETER = "p";
    public const QUERY_PARAMETER_LONG = "response-format";



    public function getTypeFromQuery(Dictionary $dictionary): ?string {
        return $dictionary->get(self::QUERY_PARAMETER)
            ?? $dictionary->get(self::QUERY_PARAMETER_LONG);
    }

    public function getIdentifier(Request $request): string {
        $header = $request->getHeader(HttpHeader::X_RESPONSE_TYPE);
        if (!is_null($header)) {
            return $this->matcher->matchContentType($header);
        }

        $queryParam = $this->getTypeFromQuery($request->getUrl()->getQuery());
        if (!is_null($queryParam)) {
            return $this->matcher->matchQuery($queryParam);
        }

        if ($request->getHttpMethod() === "GET" && App::getInstance()->getOptions()->get(App::OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET)) {
            return self::IDENT_HTML;
        }

        return self::IDENT_DEFAULT;
    }
}