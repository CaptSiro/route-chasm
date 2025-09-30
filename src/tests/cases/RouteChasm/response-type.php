<?php

use core\communication\Format;
use core\communication\FormatMatcher;
use core\communication\Request;
use core\communication\Response;
use core\communication\ResponseFormat;
use core\http\HttpHeader;
use core\RouteChasmEnvironment;
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
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, ''),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, 't'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, 'text'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, ''),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, 't'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, 'text'),
            h(Request::test(), HttpHeader::X_RESPONSE_FORMAT, ''),
            h(Request::test(), HttpHeader::X_RESPONSE_FORMAT, 'text/plain'),
            h(Request::test(), HttpHeader::X_RESPONSE_FORMAT, 'undefined'),
        ],
        Format::IDENT_HTML => [
            Request::test(),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, 'h'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, 'html'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, 'h'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, 'html'),
            h(Request::test(), HttpHeader::X_RESPONSE_FORMAT, 'text/html'),
        ],
        Format::IDENT_JSON => [
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, 'j'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT, 'json'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, 'j'),
            q(Request::test(), RouteChasmEnvironment::QUERY_RESPONSE_FORMAT_LONG, 'json'),
            h(Request::test(), HttpHeader::X_RESPONSE_FORMAT, 'application/json'),
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