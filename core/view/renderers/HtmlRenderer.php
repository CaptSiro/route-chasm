<?php

namespace core\view\renderers;

use core\Singleton;
use core\utils\Objects;
use core\view\Renderer;
use core\view\Payload;
use core\view\RendererOverride;
use core\view\ViewTemplate;
use RuntimeException;

class HtmlRenderer implements Renderer {
    use Singleton;

    public const TEMPLATE = 'html_template';



    public function render(Payload $payload): string {
        $view = $payload->getViewReference();

        if ($view instanceof RendererOverride && $view->hasRendererOverride($this, $payload)) {
            return $view->performRendererOverride($this, $payload);
        }

        if (!($view instanceof ViewTemplate)) {
            $class = Objects::getClass($view);
            $templateClass = ViewTemplate::class;

            throw new RuntimeException("Cannot render $class, because it does not implements $templateClass");
        }

        return $view->renderTemplated(
            $payload->getProperty(self::TEMPLATE)
        );
    }
}