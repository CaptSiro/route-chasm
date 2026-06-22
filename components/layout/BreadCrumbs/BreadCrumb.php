<?php

namespace components\layout\BreadCrumbs;

use core\view\Renderer;
use core\view\ViewTemplate;

class BreadCrumb implements ViewTemplate {
    use Renderer;



    /**
     * @param string $label (UNSAFE)
     * @param string|null $url
     */
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