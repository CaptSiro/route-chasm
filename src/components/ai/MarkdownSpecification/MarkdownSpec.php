<?php

namespace components\ai\MarkdownSpecification;

trait MarkdownSpec {
    public function getMarkdownSpecification(): MarkdownSpecification {
        return new MarkdownSpecification($this->role);
    }
}