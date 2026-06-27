<?php

namespace core\view2\renderers;

use core\utils\Objects;
use core\view2\Renderer;
use core\view2\Payload;
use core\view2\ViewTemplate;
use RuntimeException;

class HtmlRenderer implements Renderer {
    public const TEMPLATE = 'html_template';



    public function render(Payload $payload): string {
        $view = $payload->getViewReference();

        if (!($view instanceof ViewTemplate)) {
            $class = Objects::getClass($view);
            $templateClass = ViewTemplate::class;

            throw new RuntimeException("Cannot render $class, because it does not implements $templateClass");
        }

        return $view->renderTemplated(
            $payload->get(self::TEMPLATE)
        );
    }
}