<?php

// Location locked file

$_imported = 0;
function autoload_imported(): int {
    global $_imported;
    return $_imported;
}

function autoload_import(string $file): void {
    global $_imported;
    $_imported++;

    require_once $file;
}


// [Claude review] Performance helper for the class-map lookup in the autoloader below. Evaluated once per request.
// opcache_is_script_cached() can be missing (no OPcache) or restricted by opcache.restrict_api.
define('AUTOLOAD_HAS_OPCACHE', function_exists('opcache_is_script_cached')
    && filter_var(ini_get('opcache.enable' . (PHP_SAPI === 'cli' ? '_cli' : '')), FILTER_VALIDATE_BOOLEAN)
    && empty(ini_get('opcache.restrict_api')));

$_dirs = [
    project_mounted("<framework>"),
    project_mounted("<root>"),
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

    // [Claude review] Reject strings that are not valid class names. The name is turned directly into a file path,
    // so a dynamic name such as class_exists("..\\..\\some\\file") would otherwise require an arbitrary .php file
    // outside the source tree (path traversal -> code execution if any class name is ever user influenced).
    if (!preg_match('/^[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*$/', $class)) {
        return;
    }

    $relativePath = str_replace('\\', '/', $class) . '.php';
    if (isset($_classMap[$class])) {
        // [Claude review] The class map is persisted to disk, so after a class file is moved or deleted the stale
        // entry made require_once fail fatally on every request until class-map.json was deleted by hand.
        // Stale entries are now dropped and the normal lookup below runs instead.
        // [Claude review] Performance: a plain file_exists() here added ~300 stat calls per request (~5-13 ms on
        // Windows). opcache_is_script_cached() is an in-memory lookup (~200x cheaper); OPcache already revalidates
        // cached scripts against disk itself, so we only stat files that OPcache does not know yet.
        $cached = $_classMap[$class];
        if ((AUTOLOAD_HAS_OPCACHE && opcache_is_script_cached($cached)) || file_exists($cached)) {
            $_classMapHits++;
            autoload_import($_classMap[$class]);
            return;
        }

        unset($_classMap[$class]);
        global $_classMapWrites;
        $_classMapWrites++;
    }

    $file = __DIR__ . "/../$relativePath";

    if (file_exists($file)) {
        autoload_addCacheRecord($file, $class);
        autoload_import($file);
        return;
    }

    foreach ($_dirs as $dir) {
        if (is_null($dir)) {
            continue;
        }

        $path = "$dir/$relativePath";
        if (!file_exists($path)) {
            continue;
        }

        autoload_addCacheRecord($path, $class);
        autoload_import($path);
        return;
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
        autoload_import($entryFile);
        return;
    }

    http_response_code(500);
    echo '<pre>';
    // [Claude review] debug_backtrace() included every call's arguments (objects, DB config, request data...) and
    // was echoed unescaped into HTML: information disclosure + XSS. Arguments are now omitted and output escaped.
    echo htmlspecialchars(json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), JSON_PRETTY_PRINT));
    echo '</pre>';
    echo "[Critical Error]: Class does not exists (" . htmlspecialchars($class) . ')';
    exit;
});