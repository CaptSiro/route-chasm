<?php

namespace core\view;

interface Render {
    public function render(?string $template = null): string;

    public function getRoot(): Render;
}