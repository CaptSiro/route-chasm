<?php

namespace core\cache;

/**
 * @template-implements Cache<string>
 */
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
            throw new FileAccessException($this->file);
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

    public function set(string $variable, mixed $value): static {
        $this->load();
        $this->internal[$variable] = $value;
        return $this;
    }

    public function delete(string $variable): static {
        $this->load();
        unset($this->internal[$variable]);
        return $this;
    }

    public function save(): static {
        if (!$this->isLoaded) {
            return $this;
        }

        if (!file_exists($this->file)) {
            touch($this->file);
        }

        $fp = fopen($this->file, 'w');
        if ($fp === false) {
            throw new FileAccessException($this->file);
        }

        fwrite($fp, $this->toString());
        fclose($fp);
        return $this;
    }

    public function toString(): string {
        $buffer = '';

        foreach ($this->internal as $variable => $value) {
            $buffer .= $variable .'='. $value . PHP_EOL;
        }

        return $buffer;
    }
}