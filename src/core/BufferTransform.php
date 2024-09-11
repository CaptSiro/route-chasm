<?php

namespace core;

class BufferTransform {
    public function __construct(
        protected string $contents = ''
    ) {}



    /**
     * @return string
     */
    public function getContents(): string {
        return $this->contents;
    }

    /**
     * @param string $contents
     */
    public function setContents(string $contents): void {
        $this->contents = $contents;
    }
}