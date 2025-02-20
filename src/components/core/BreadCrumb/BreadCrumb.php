<?php

namespace components\core\BreadCrumb;

use core\view\Renderer;
use core\view\View;

class BreadCrumb implements View {
    use Renderer;

    public function __construct(
        protected string $label,
        protected ?string $url = null
    ) {}



    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setUrl(?string $url): void {
        $this->url = $url;
    }
}