<?php

namespace components\core\BreadCrumbs;

use core\view\Renderer;
use core\view\View;

class BreadCrumb implements View {
    use Renderer;



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