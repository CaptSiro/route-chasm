<?php

namespace core\view2;

interface Renderer {
    public function render(Payload $payload): string;
}