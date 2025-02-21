<?php

namespace core\html;

interface Attribute {
    public function addAttribute(string $name, mixed $value = null): static;

    public function getAttributes(): array;

    public function stringifyAttributes(): string;

    public function getAttribute(string $name): mixed;

    public function addJavascriptInit(string $function): static;

    public function addCssClass(string $class): static;

    public function getCssClass(): string;
}