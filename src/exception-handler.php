<?php

function exc_dump_array(array $array): void {
    foreach ($array as $key => $value) {
        echo "<code>
            <span class=\"key\">$key</span>
            <span class=\"separator\"> => </span>
            <span class=\"value\">$value</span>
        </code>";
    }
}



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