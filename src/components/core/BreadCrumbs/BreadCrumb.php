<?php

namespace components\core\BreadCrumbs;

class BreadCrumb {
    public function __construct(
        protected string $label,
        protected ?string $url = null
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setUrl(?string $url): void {
        $this->url = $url;
    }
}