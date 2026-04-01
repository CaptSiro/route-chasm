<?php

namespace core\io;

use Generator;

class FileReader {
    protected mixed $handle;



    /**
     * Opens the file when constructed
     *
     * @param string $path
     */
    public function __construct(
        protected string $path,
    ) {
        $this->handle = fopen($this->path, "r");
    }

    public function __destruct() {
        if (!$this->checkHandleValidity()) {
            return;
        }

        fclose($this->handle);
    }



    protected function checkHandleValidity(): bool {
        return $this->handle !== false;
    }

    protected function falseToEmpty(false|string $value): string {
        if ($value === false) {
            return '';
        }

        return $value;
    }

    public function isEndOfFile(): bool {
        return !$this->checkHandleValidity()
            || feof($this->handle);
    }

    public function read(int $length): string {
        if (!$this->checkHandleValidity()) {
            return '';
        }

        return $this->falseToEmpty(
            fread($this->handle, $length)
        );
    }

    public function readAll(): string {
        return file_get_contents($this->path);
    }

    public function readCharacter(): string {
        if (!$this->checkHandleValidity()) {
            return '';
        }

        return $this->falseToEmpty(
            fgetc($this->handle)
        );
    }

    public function seek(int $offset): int {
        return fseek($this->handle, $offset);
    }

    public function getPosition(): int {
        return ftell($this->handle);
    }

    public function readLine(bool $trimNewLine = false): string {
        if (!$this->checkHandleValidity()) {
            return '';
        }

        $line = $this->falseToEmpty(
            fgets($this->handle)
        );

        if ($trimNewLine) {
            return rtrim($line, "\n\r");
        }

        return $line;
    }

    /**
     * @return Generator<string>
     */
    public function lines(bool $trimNewLine = false): Generator {
        while (!$this->isEndOfFile()) {
            yield $this->readLine($trimNewLine);
        }
    }
}