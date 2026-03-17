<?php

namespace core\data;

class DataItem {
    private ?string $content;
    private bool $isDeleted = false;

    public function __construct(
        protected string $namespace,
        protected string $file,
        protected DataWritePolicy $policy = DataWritePolicy::WRITE_THROUGH
    ) {}

    public function __destruct() {
        if ($this->policy === DataWritePolicy::WRITE_BACK && isset($this->content) && !$this->isDeleted) {
            Data::store($this->namespace, $this->file, $this->content);
        }
    }



    public function getFilePath(): string {
        return Data::file($this->namespace, $this->file);
    }

    public function exists(): bool {
        return file_exists($this->getFilePath());
    }

    public function read(): ?string {
        if (!isset($this->content)) {
            $this->content = Data::retrieve($this->namespace, $this->file);
        }

        return $this->content;
    }

    public function write(mixed $content): void {
        $this->isDeleted = false;
        $this->content = $content;

        if ($this->policy === DataWritePolicy::WRITE_THROUGH) {
            Data::store($this->namespace, $this->file, $this->content);
        }
    }

    public function delete(): bool {
        $this->isDeleted = true;
        return Data::delete($this->namespace, $this->file);
    }
}