<?php

namespace core\view\renderers;

use core\utils\Objects;
use core\view\Renderer;
use core\view\Payload;
use core\view\RendererOverride;
use JsonSerializable;
use RuntimeException;

class JsonRenderer implements Renderer {
    public function render(Payload $payload): string {
        $view = $payload->getViewReference();

        if ($view instanceof RendererOverride && $view->hasRendererOverride($this, $payload)) {
            return $view->performRendererOverride($this, $payload);
        }

        if (!($view instanceof JsonSerializable)) {
            $class = Objects::getClass($view);
            $jsonClass = JsonSerializable::class;

            throw new RuntimeException("Cannot render $class, because it does not implements $jsonClass");
        }

        return json_encode($view);
    }
}