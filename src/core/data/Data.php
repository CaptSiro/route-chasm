<?php

namespace core\data;

use core\route\Path;
use core\RouteChasmEnvironment;

class Data {
    public static function namespace(string $ns, bool $create = false): string {
        $directory = Path::join(RouteChasmEnvironment::DIRECTORY_DATA, $ns);

        if ($create && !file_exists($directory)) {
            mkdir($directory, recursive: true);
        }

        return $directory;
    }

    public static function file(string $namespace, string $file): string {
        $path = Path::join(RouteChasmEnvironment::DIRECTORY_DATA, $namespace, $file);
        $directory = dirname($path);

        if (!file_exists($directory)) {
            mkdir($directory, recursive: true);
        }

        return $path;
    }

    public static function store(string $namespace, string $file, mixed $data): false|int {
        return file_put_contents(
            self::file($namespace, $file),
            $data
        );
    }

    public static function retrieve(string $namespace, string $file): ?string {
        $file = self::file($namespace, $file);
        if (!file_exists($file)) {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        return $contents;
    }

    public static function delete(string $namespace, string $file): bool {
        $location = self::file($namespace, $file);
        if (!file_exists($location)) {
            return false;
        }

        return unlink($location);
    }
}