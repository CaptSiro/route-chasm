<?php

namespace core\cache;

class LazyFileCache implements Cache {
    public const KEY_VALUE_SEPARATOR = '=';

    protected bool $isLoaded;
    protected array $internal;



    public function __construct(
        protected string $file
    ) {
        $this->isLoaded = false;
        $this->internal = [];
    }



    protected function load(): void {
        if ($this->isLoaded) {
            return;
        }

        $this->isLoaded = true;

        if (!file_exists($this->file)) {
            return;
        }

        $fp = fopen($this->file, 'r');
        if ($fp === false) {
            throw new FileAccessException();
        }

        while (($line = fgets($fp)) !== false) {
            $index = strpos($line, self::KEY_VALUE_SEPARATOR);
            if ($index === false) {
                continue;
            }

            $this->internal[substr($line, 0, $index)] = substr(rtrim($line), $index + 1);
        }

        fclose($fp);
    }

    public function has(string $variable): bool {
        $this->load();
        return isset($this->internal[$variable]);
    }

    public function get(string $variable): string {
        $this->load();
        return $this->internal[$variable];
    }

    public function set(string $variable, string $value): Cache {
        $this->load();
        $this->internal[$variable] = $value;
        return $this;
    }

    public function delete(string $variable): Cache {
        $this->load();
        unset($this->internal[$variable]);
        return $this;
    }

    public function save(): Cache {
        if (!$this->isLoaded) {
            return $this;
        }

        if (!file_exists($this->file)) {
            touch($this->file);
        }

        $fp = fopen($this->file, 'w');
        if ($fp === false) {
            throw new FileAccessException();
        }

        foreach ($this->internal as $variable => $value) {
            fputs($fp, $variable .'='. $value . PHP_EOL);
        }

        fclose($fp);
        return $this;
    }
}