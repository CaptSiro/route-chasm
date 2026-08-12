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

$_classMapPath = project_mounted("<storage>") . '/class-map.json';
$_classMap = [];
$_classMapWrites = 0;
$_classMapHits = 0;

if (file_exists($_classMapPath)) {
    $_classMap = json_decode(file_get_contents($_classMapPath), associative: true);
}

function autoload_addCacheRecord(string $file, string $class): void {
    global $_classMap, $_classMapWrites;

    $_classMap[$class] = $file;
    $_classMapWrites++;
}

function autoload_saveCache(): void {
    global $_classMapPath, $_classMap, $_classMapWrites;

    if ($_classMapWrites === 0) {
        return;
    }

    file_put_contents(
        $_classMapPath,
        json_encode($_classMap, JSON_PRETTY_PRINT),
    );
}

function autoload_cacheHits(): int {
    global $_classMapHits;
    return $_classMapHits;
}



spl_autoload_register(function ($class) {
    global $_dirs, $_classMap, $_classMapHits;

    $relativePath = str_replace('\\', '/', $class) . '.php';
    if (isset($_classMap[$class])) {
        $_classMapHits++;
        autoload_import($_classMap[$class], $class);
        return;
    }

    $file = __DIR__ . "/../$relativePath";

    if (file_exists($file)) {
        autoload_addCacheRecord($file, $class);
        autoload_import($file, $class);
        return;
    }

    foreach ($_dirs as $dir) {
        $path = "$dir/$relativePath";
        if (is_null($dir) || !file_exists($path)) {
            continue;
        }

        autoload_addCacheRecord($path, $class);
        autoload_import($path, $class);
    }

    foreach (scandir(DIRECTORY_REPOSITORY) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $entryFile = DIRECTORY_REPOSITORY ."/$entry/$relativePath";
        if (!file_exists($entryFile)) {
            continue;
        }

        autoload_addCacheRecord($entryFile, $class);
        autoload_import($entryFile, $class);
        return;
    }

    http_response_code(500);
    echo '<pre>';
    echo json_encode(debug_backtrace(), JSON_PRETTY_PRINT);
    echo '</pre>';
    echo "[Critical Error]: Class does not exists (" . htmlspecialchars($class) . ')';
    exit;
});