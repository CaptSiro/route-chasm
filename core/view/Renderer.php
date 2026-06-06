<?php

namespace core\view;

trait Renderer {
    use TemplateRenderer;

    public function render(): string {
        return $this->renderTemplated();
    }

    public function getRoot(): View {
        return $this;
    }
}