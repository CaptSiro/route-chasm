<?php

/** only files containing constants are allowed to be imported to this critical part of the code */
use core\http\HttpHeader;
use core\RouteChasmEnvironment;



function exc_dump_array(array $array): void {
    foreach ($array as $key => $value) {
        $v = json_encode($value);

        echo "<code>
            <span class=\"key\">$key</span>
            <span class=\"separator\"> => </span>
            <span class=\"value\">$v</span>
        </code>";
    }
}



function get_response_format(): string {
    $headers = apache_request_headers();
    return strtolower($_GET[RouteChasmEnvironment::QUERY_RESPONSE_FORMAT]
        ?? $headers[HttpHeader::X_RESPONSE_FORMAT]
        ?? 'html');
}

function error_handler($severity, $message, $file, $line): void {
    if (RouteChasmEnvironment::ERROR_SEVERITY_BLACKLIST & $severity > 0) {
        return;
    }

    while (ob_get_level()) {
        ob_get_clean();
    }

    http_response_code(500);

    $responseType = strtolower(get_response_format());

    if ($responseType === 'json' || $responseType === 'application/json' || $responseType === 'j') {
        header('Content-Type: application/json');
        echo json_encode([
            'type' => 'error',
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ]);
        exit();
    }

    require __DIR__ . '/error.phtml';
    exit();
}

function exception_handler($exception): void {
    while (ob_get_level()) {
        ob_get_clean();
    }

    http_response_code(500);

    $responseType = strtolower(get_response_format());

    if ($responseType === 'json' || $responseType === 'application/json' || $responseType === 'j') {
        header('Content-Type: application/json');

        $stacktrace = [];
        foreach ($exception->getTrace() as $trace) {
            if (isset($trace['file'])) {
                $stacktrace[] = $trace;
            }
        }

        echo json_encode([
            'type' => 'exception',
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => $stacktrace,
        ]);

        exit();
    }

    require __DIR__ . '/exception.phtml';
    exit();
}

set_error_handler('error_handler');
set_exception_handler('exception_handler');