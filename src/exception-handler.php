<?php

function exceptions_error_handler($severity, $message, $filename, $lineno): void {
    echo "Severity: $severity; Message: $message; File: $filename; Line: $lineno";
    exit();
}

set_error_handler('exceptions_error_handler');