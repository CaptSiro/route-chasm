<?php

namespace core\view;

interface RendererOverride {
    public function hasRendererOverride(Renderer $renderer, Payload $payload): bool;

    public function performRendererOverride(Renderer $renderer, Payload $payload): string;
}