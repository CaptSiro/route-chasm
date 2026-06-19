<?php

namespace core\communication;

use core\App;
use core\collections\Dictionary;
use core\http\HttpHeader;
use core\RouteChasmEnvironment;

class ResponseFormat implements Format {
    use BaseFormat;



    public function getTypeFromQuery(Dictionary $dictionary): ?string {
        return $dictionary->get(RouteChasmEnvironment::QUERY_RESPONSE_FORMAT)
            ?? $dictionary->get(RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG);
    }

    public function getIdentifier(Request $request): string {
        $header = $request->getHeader(HttpHeader::X_RESPONSE_FORMAT);
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