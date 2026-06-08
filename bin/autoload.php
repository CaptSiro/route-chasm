<?php

// Location locked file

$_imported = 0;
function autoload_imported(): int {
    global $_imported;
    return $_imported;
}

function autoload_import(string $file, string $class): void {
    global $_imported;
    $_imported++;

    require_once $file;

    if (method_exists($class, "init")) {
        try {
            call_user_func("$class::init");
        } catch (TypeError) {
            // abstract class extends init method but does not provide override
        }
    }
}



$_dirs = [
    project_mounted("<framework>"),
    project_mounted("<project>"),
];



spl_autoload_register(function ($class) {
    global $_dirs;

    $relativePath = str_replace('\\', '/', $class) . '.php';
    $file = __DIR__ . "/../$relativePath";

    if (file_exists($file)) {
        autoload_import($file, $class);
        return;
    }

    foreach ($_dirs as $dir) {
        if (is_null($dir) || !file_exists("$dir/$relativePath")) {
            continue;
        }

        autoload_import("$dir/$relativePath", $class);
    }

    foreach (scandir(DIRECTORY_REPOSITORY) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $entryFile = DIRECTORY_REPOSITORY ."/$entry/$relativePath";
        if (!file_exists($entryFile)) {
            continue;
        }

        autoload_import($entryFile, $class);
        return;
    }

    http_response_code(500);
    echo "[Critical Error]: Class does not exists (" . htmlspecialchars($class) . ')';
    exit;
});