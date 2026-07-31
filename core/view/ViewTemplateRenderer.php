<?php

namespace core\view;

trait ViewTemplateRenderer {
    use ViewTemplateTrait;



    public function render(): string {
        return $this->renderTemplated();
    }

    public function __toString(): string {
        return $this->render();
    }
}