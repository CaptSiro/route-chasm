<?php

namespace core\view;

trait Renderer {
    use TemplateRenderer;

    public function getRoot(): View {
        return $this;
    }
}