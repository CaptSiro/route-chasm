<?php

namespace core\view2\renderers;

use core\utils\Objects;
use core\view2\Component;
use core\view2\Renderer;
use core\view2\Payload;
use core\view2\ViewTemplate;
use RuntimeException;

class XmlRenderer implements Renderer {
    public const TEMPLATE = 'xml_template';

    public static function getXmlTemplate(ViewTemplate $view): string {
        return $view->getTemplate(".xml.php");
    }

    public static function setXmlTemplate(Component $component): void {
        $component->set(self::TEMPLATE, self::getXmlTemplate($component));
    }



    public function render(Payload $payload): string {
        $view = $payload->getViewReference();

        if (!($view instanceof ViewTemplate)) {
            $class = Objects::getClass($view);
            $templateClass = ViewTemplate::class;

            throw new RuntimeException("Cannot render $class, because it does not implements $templateClass");
        }

        if (is_null($template = $payload->get(self::TEMPLATE))) {
            $property = self::TEMPLATE;
            throw new RuntimeException("Payload must include explicit property '$property' with template");
        }

        return $view->renderTemplated(
            $payload->get(self::TEMPLATE)
        );
    }
}