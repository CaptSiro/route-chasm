<?php

namespace core\communication;

use JsonSerializable;
use retval\exceptions\Exc;
use retval\Result;

readonly class UploadedFile implements JsonSerializable {
    public function __construct(
        public string $name,
        public string $type,
        public int $size,
        public int $error,
        private ?string $temporaryName = null,
    ) {}

    public function move(string $destination): Result {
        if ($this->error !== UPLOAD_ERR_OK) {
            return Result::fail(new Exc("Error occurred when uploading file: '$this->name'. Code: '$this->error'"));
        }

        if (is_null($this->temporaryName)) {
            return Result::fail(new Exc("Uploaded file '$this->name' has not been uploaded properly. No temporary file"));
        }

        move_uploaded_file($this->temporaryName, $destination);
        return Result::success(true);
    }

    public function jsonSerialize(): array {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
            'error' => $this->error,
            'temporaryName' => $this->temporaryName,
        ];
    }
}