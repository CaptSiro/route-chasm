<?php

namespace core\view;

trait Renderer {
    use TemplateRenderer;

    public function getRoot(): Render {
        return $this;
    }
}