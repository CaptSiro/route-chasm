<?php

use core\communication\Format;
use core\communication\FormatMatcher;
use core\communication\Request;
use core\communication\Response;
use core\communication\ResponseFormat;
use core\http\HttpHeader;
use sptf\Sptf;

function q(Request $request, string $name, string $value): Request {
    $request->getUrl()->getQuery()->set($name, $value);
    return $request;
}

function h(Request $request, string $header, string $value): Request {
    $request->setHeader($header, $value);
    return $request;
}

Sptf::test("should detect response type from request", function () {
    $requests = [
        Format::IDENT_TEXT => [
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, ''),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, 't'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, 'text'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, ''),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, 't'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, 'text'),
            h(Request::test(), HttpHeader::X_RESPONSE_TYPE, ''),
            h(Request::test(), HttpHeader::X_RESPONSE_TYPE, 'text/plain'),
            h(Request::test(), HttpHeader::X_RESPONSE_TYPE, 'undefined'),
        ],
        Format::IDENT_HTML => [
            Request::test(),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, 'h'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, 'html'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, 'h'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, 'html'),
            h(Request::test(), HttpHeader::X_RESPONSE_TYPE, 'text/html'),
        ],
        Format::IDENT_JSON => [
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, 'j'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER, 'json'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, 'j'),
            q(Request::test(), ResponseFormat::QUERY_PARAMETER_LONG, 'json'),
            h(Request::test(), HttpHeader::X_RESPONSE_TYPE, 'application/json'),
        ]
    ];

    $response = new Response((new ResponseFormat())->setFormatMatcher(new FormatMatcher()));

    foreach ($requests as $type => $arr) {
        foreach ($arr as $request) {
            /** @var Request $request */
            Sptf::expect($response->getFormat($request))
                ->toBe($type);
        }
    }
});