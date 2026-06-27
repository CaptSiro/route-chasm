<?php

namespace core\view2;

use core\view2\renderers\HtmlRenderer;

class Component implements ViewTemplate, Payload {
    use ViewTemplateTrait, PayloadTrait;



    private string $title;

    public function __construct(
        protected Renderer $renderer = new HtmlRenderer()
    ) {}



    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): static {
        $this->title = $title;
        $this[Head::PROPERTY_TITLE] = $title;
        return $this;
    }

    public function setRenderer(Renderer $renderer): static {
        $this->renderer = $renderer;
        return $this;
    }

    public function render(): string {
        return $this->renderer->render($this);
    }

    public function __toString(): string {
        return $this->render();
    }
}