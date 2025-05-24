<?php

namespace core\communication;


use core\collections\Dictionary;
use core\http\HttpHeader;

class RequestFormat implements Format {
    use BaseFormat;

    public const QUERY_PARAMETER = "q";
    public const QUERY_PARAMETER_LONG = "request-format";



    public function getTypeFromQuery(Dictionary $dictionary): ?string {
        return $dictionary->get(self::QUERY_PARAMETER)
            ?? $dictionary->get(self::QUERY_PARAMETER_LONG);
    }

    public function getIdentifier(Request $request): string {
        $header = $request->getHeader(HttpHeader::X_REQUEST_TYPE);
        if (!is_null($header)) {
            return $this->matcher->matchContentType($header);
        }

        $queryParam = $this->getTypeFromQuery($request->getUrl()->getQuery());
        if (!is_null($queryParam)) {
            return $this->matcher->matchQuery($queryParam);
        }

        $contentType = $request->getHeader(HttpHeader::CONTENT_TYPE);
        if (!is_null($contentType)) {
            $position = strpos($contentType, ';');
            return $this->matcher->matchContentType($position === false
                ? $contentType
                : substr($contentType, 0, $position));
        }

        return self::IDENT_DEFAULT;
    }
}