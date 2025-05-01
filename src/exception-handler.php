<?php
/** @noinspection PhpUnusedParameterInspection */

/** @noinspection PhpUnusedParameterInspection */

/** @noinspection PhpUnusedParameterInspection */

/** @noinspection PhpUnusedParameterInspection */

/** @noinspection PhpUnusedParameterInspection */

function error_handler($severity, $message, $file, $line): void {
    while (ob_get_level()) {
        ob_get_clean();
    }

    require __DIR__ . '/error.phtml';
    exit();
}

function exception_handler($exception): void {
    while (ob_get_level()) {
        ob_get_clean();
    }

    require __DIR__ . '/exception.phtml';
    exit();
}

set_error_handler('error_handler');
set_exception_handler('exception_handler');