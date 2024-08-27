<?php

use core\Http;
use core\Request;
use sptf\Sptf;

function q(Request $request, string $name, string $value): Request {
    $request->url->query->set($name, $value);
    return $request;
}

function h(Request $request, string $header, string $value): Request {
    $request->setTestHeader($header, $value);
    return $request;
}

Sptf::test("should detect response type from request", function () {
    $requests = [
        'TEXT' => [
            q(Request::test(), 't', ''),
            q(Request::test(), 't', 't'),
            q(Request::test(), 't', 'text'),
            q(Request::test(), 'type', ''),
            q(Request::test(), 'type', 't'),
            q(Request::test(), 'type', 'text'),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, ''),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, 't'),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, 'text'),
        ],
        'HTML' => [
            Request::test(),
            q(Request::test(), 't', 'h'),
            q(Request::test(), 't', 'html'),
            q(Request::test(), 'type', 'h'),
            q(Request::test(), 'type', 'html'),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, 'h'),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, 'html'),
        ],
        'JSON' => [
            q(Request::test(), 't', 'j'),
            q(Request::test(), 't', 'json'),
            q(Request::test(), 'type', 'j'),
            q(Request::test(), 'type', 'json'),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, 'j'),
            h(Request::test(), Http::HEADER_X_RESPONSE_TYPE, 'json'),
        ]
    ];

    foreach ($requests as $type => $arr) {
        foreach ($arr as $request) {
            /** @var Request $request */
            Sptf::expect($request->getResponseType())
                ->toBe($type);
        }
    }
});