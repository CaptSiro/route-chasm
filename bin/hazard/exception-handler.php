<?php

// Location locked file

// Only files containing constants are allowed to be imported to this critical part of the code
use core\http\HttpHeader;
use core\RouteChasmEnvironment;



function exc_dump_array_pretty(array $array, ?string $label = null): void {
    $label = is_null($label)
        ? htmlspecialchars("[generic array]")
        : htmlspecialchars($label);

    if (($count = count($array)) === 0) {
        echo "
            <div class=\"acc-group\">
                <button type=\"button\" class=\"acc-trigger\" aria-expanded=\"true\">
                    <svg class=\"chev\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.2\"
                         stroke-linecap=\"round\" stroke-linejoin=\"round\">
                        <path d=\"M9 5l7 7-7 7\"></path>
                    </svg>
                    <span class=\"var-name\">$label</span><span class=\"var-count\">$count</span></button>
                <div class=\"acc-content open\">
                    <div class=\"kv-empty\">Empty</div>
                </div>
            </div>";
        return;
    }

    echo "
        <div class=\"acc-group\">
        <button type=\"button\" class=\"acc-trigger\" aria-expanded=\"true\">
            <svg class=\"chev\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.2\"
                 stroke-linecap=\"round\" stroke-linejoin=\"round\">
                <path d=\"M9 5l7 7-7 7\"></path>
            </svg>
            <span class=\"var-name\">$label</span><span class=\"var-count\">44</span></button>
        <div class=\"acc-content open\">
            <div class=\"kv-table\">
    ";

    foreach ($array as $key => $value) {
        $_key = htmlspecialchars($key);
        $_value = htmlspecialchars(json_encode($value));

        echo "
                <div class=\"kv-row\">
                    <span class=\"kv-key\">$_key</span>
                    <span class=\"kv-sep\">=&gt;</span>
                    <span class=\"kv-val\">$_value</span>
                </div>
        ";
    }

    echo "
            </div>
        </div>";
}



class __internal_Hazard {
    public function __construct(
        public Exception|Error|null $exception = null,
        public mixed $severity = null,
        public mixed $message = null,
        public mixed $file = null,
        public mixed $line = null
    ) {}

    public function isException(): true {
        return !is_null($this->exception);
    }

    public function getMessage(): string {
        return $this->isException()
            ? $this->exception->getMessage()
            : $this->message;
    }

    public function getType(): string {
        return $this->isException()
            ? get_class($this->exception)
            : match ($this->severity) {
                E_USER_ERROR => 'Error',
                E_USER_WARNING => 'Warning',
                E_USER_NOTICE => 'Notice',
                default => 'Unknown Error',
            };
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
    header("X-Internal-Server-Error: $message ($file:$line)");

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

    $hazard = new __internal_Hazard(null, $severity, $message, $file, $line);
    require __DIR__ . '/hazard.phtml';
    exit();
}

function exception_handler($exception): void {
    while (ob_get_level()) {
        ob_get_clean();
    }

    http_response_code(500);

    $message = $exception->getMessage();
    $t = $exception->getTrace();
    if (isset($t[0])) {
        $t = $t[0];

        if (isset($t['file']) && isset($t['line'])) {
            header("X-Internal-Server-Error: $message ($t[file]:$t[line])");
        } else {
            header("X-Internal-Server-Error: $message");
        }
    } else {
        header("X-Internal-Server-Error: $message");
    }

    $responseType = strtolower(get_response_format());

    if ($responseType === 'json' || $responseType === 'application/json' || $responseType === 'j') {
        header('Content-Type: application/json');

        $stacktrace = [];
        foreach ($exception->getTrace() as $trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
                $stacktrace[] = [
                    'file' => $trace['file'],
                    'line' => $trace['line'],
                ];
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

    $hazard = new __internal_Hazard($exception);
    require __DIR__ . '/hazard.phtml';
    exit();
}

set_error_handler('error_handler');
set_exception_handler('exception_handler');