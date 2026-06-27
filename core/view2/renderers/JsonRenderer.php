<?php

namespace core\view2\renderers;

use core\utils\Objects;
use core\view2\Renderer;
use core\view2\Payload;
use RuntimeException;

class JsonRenderer implements Renderer {
    public function render(Payload $payload): string {
        $view = $payload->getViewReference();

        if (!($view instanceof JsonRenderer)) {
            $class = Objects::getClass($view);
            $jsonClass = JsonRenderer::class;

            throw new RuntimeException("Cannot render $class, because it does not implements $jsonClass");
        }

        return json_encode($view);
    }
}