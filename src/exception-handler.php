<?php

function error_handler($severity, $message, $file, $line): void {
    require __DIR__ . '/error.phtml';
    exit();
}

function exception_handler($exception): void {
    require __DIR__ . '/exception.phtml';
    exit();
}

set_error_handler('error_handler');
set_exception_handler('exception_handler');