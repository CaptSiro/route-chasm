<?php

namespace core\view;

interface Renderer {
    public function render(Payload $payload): string;
}