<?php

namespace core\view\renderers;

use core\utils\Objects;
use core\view\Component;
use core\view\Payload;
use core\view\Renderer;
use core\view\RendererOverride;
use core\view\ViewTemplate;
use RuntimeException;

class XmlRenderer implements Renderer {
    public const TEMPLATE_EXTENSION = '.xml.php';

    public const TEMPLATE = 'xml_template';

    public static function getXmlTemplate(ViewTemplate $view): string {
        return $view->getTemplate(self::TEMPLATE_EXTENSION);
    }

    public static function setXmlTemplate(Component $component): void {
        $component->setProperty(self::TEMPLATE, self::getXmlTemplate($component));
    }



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

        if (is_null($template = $payload->getProperty(self::TEMPLATE))) {
            $property = self::TEMPLATE;
            throw new RuntimeException("Payload must include explicit property '$property' with template");
        }

        return $view->renderTemplated(
            $payload->getProperty(self::TEMPLATE)
        );
    }
}