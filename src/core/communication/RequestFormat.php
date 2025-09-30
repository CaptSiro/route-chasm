<?php

namespace core\communication;


use core\collections\Dictionary;
use core\http\HttpHeader;
use core\RouteChasmEnvironment;

class RequestFormat implements Format {
    use BaseFormat;



    public function getTypeFromQuery(Dictionary $dictionary): ?string {
        return $dictionary->get(RouteChasmEnvironment::QUERY_REQUEST_FORMAT)
            ?? $dictionary->get(RouteChasmEnvironment::QUERY_REQUEST_FORMAT_LONG);
    }

    public function getIdentifier(Request $request): string {
        $header = $request->getHeader(HttpHeader::X_REQUEST_FORMAT);
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