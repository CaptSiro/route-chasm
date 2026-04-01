<?php

namespace core\communication;

use JsonSerializable;
use retval\exceptions\Exc;
use retval\Result;

class UploadedFile implements JsonSerializable {
    public function __construct(
        protected string $name,
        protected string $type,
        protected int $size,
        protected int $error,
        protected ?string $temporaryPath = null,
    ) {}

    public function __destruct() {
        if (!is_null($this->temporaryPath) && file_exists($this->temporaryPath)) {
            unlink($this->temporaryPath);
        }
    }



    public function getPath(): ?string {
        return $this->temporaryPath;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getError(): int {
        return $this->error;
    }

    public function move(string $destination): Result {
        if ($this->error !== UPLOAD_ERR_OK) {
            return Result::fail(new Exc("Error occurred when uploading file: '$this->name'. Code: '$this->error'"));
        }

        if (is_null($this->temporaryPath)) {
            return Result::fail(new Exc("Uploaded file '$this->name' has not been uploaded properly. No temporary file"));
        }

        $directory = dirname($destination);
        if (!file_exists($directory)) {
            mkdir($directory, recursive: true);
        }

        if (!rename($this->temporaryPath, $destination)) {
            return Result::fail(new Exc("Cannot move uploaded file '$this->name'. Unknown reason."));
        }

        return Result::success(true);
    }

    public function jsonSerialize(): array {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
            'error' => $this->error,
            'temporaryName' => $this->temporaryPath,
        ];
    }
}