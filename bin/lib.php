<?php

// Location locked file

const FILE_PROJECT_JSON = __DIR__ . '/../project.json';
const DIRECTORY_REPOSITORY = __DIR__ . '/..';



function is_cli(): bool {
    return php_sapi_name() === 'cli';
}

define("EOL", is_cli() ? PHP_EOL : "<br>");



function error(string $message): void {
    echo "[Error]: $message" . EOL;
    exit;
}

function message(string $message): void {
    echo "[Log]: $message" . EOL;
}

function execute(string $command, bool $simulate = true): int {
    echo "> $command" . EOL;

    if (defined('SIMULATE') || $simulate) {
        return 0;
    }

    exec($command, $output, $code);
    return $code;
}



$lib_project_file = null;
$lib_project_json = null;

function project_json(string $file = FILE_PROJECT_JSON): ?array {
    global $lib_project_json, $lib_project_file;
    if (!is_null($lib_project_json) && $lib_project_file === $file) {
        return $lib_project_json;
    }

    if (($content = file_get_contents($file)) === false) {
        return null;
    }

    $lib_project_file = $file;
    return $lib_project_json = json_decode($content, associative: true);
}

function json_get(array $json, string $path): mixed {
    $properties = array_filter(
        array_map(
            fn($x) => trim($x),
            explode('.', $path)
        ),
        fn($x) => !empty($x)
    );

    $current = $json;
    foreach ($properties as $property) {
        if (!is_array($current)) {
            return null;
        }

        if (is_null($value = $current[$property] ?? null)) {
            return null;
        }

        $current = $value;
    }

    return $current;
}

function project_get(string $path, ?array $json = null): mixed {
    return json_get($json ?? project_json(), $path);
}

function json_get_or_die(array $json, string $path, ?string $file = null): mixed {
    if (is_null($value = json_get($json, $path))) {
        if (is_null($file)) {
            error("$path is not set");
            exit;
        }

        error("$path is not set in $file");
        exit;
    }

    return $value;
}

function project_get_or_die(string $path, ?array $json = null, ?string $file = null): mixed {
    return json_get_or_die($json ?? project_json(), $path, $file);
}

function project_mounted(string $path, ?array $json = null): ?string {
    $json ??= project_json();

    if (is_null($json)) {
        return null;
    }

    if (preg_match("/.*<([a-zA-Z0-9-_]+)>.*/", $path, $matches)) {
        if (!is_null($mount = json_get($json, "mount.$matches[1]"))) {
            if (trim($mount) === "./") {
                $mount = __DIR__ .'/..';
            }

            $path = str_replace("<$matches[1]>", $mount, $path);
        }
    }

    if (str_starts_with($path, "./")) {
        $path = __DIR__ .'/..'. substr($path, 1);
    }

    if (($real = realpath($path)) === false) {
        return null;
    }

    return $real;
}

function project_version(): string {
    $versionFile = __DIR__ .'/../VERSION';

    if (!is_null($ver = project_mounted("<framework>/VERSION"))) {
        $versionFile = $ver;
    }

    if (!file_exists($versionFile)) {
        return "Unknown version";
    }

    return file_get_contents($versionFile);
}
