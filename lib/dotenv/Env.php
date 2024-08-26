<?php

namespace dotenv;

readonly class Env {
    public static function fromFile(string $file): ?Env {
        $map = [
            "__ENV_FILE__" => $file
        ];

        $handle = fopen($file, "r");
        if ($handle === false) {
            return null;
        }

        while (($line = fgets($handle)) !== false) {
            if (preg_match("/^([a-zA-Z_]+[a-zA-Z0-9_]*)=([^#]*).*/", rtrim($line), $matches)) {
                $map[$matches[1]] = rtrim($matches[2]);
            }
        }

        fclose($handle);
        return new self($map);
    }



    function __construct(
        private array $map
    ) {}



    public function get(string $name): ?string {
        return $this->map[$name] ?? null;
    }



    function __get($name) {
        if (!isset($this->map[$name])) {
            return null;
        }

        return $this->map[$name];
    }
}