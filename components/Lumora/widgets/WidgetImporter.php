<?php

namespace components\Lumora\widgets;

use core\ResourceLoader;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class WidgetImporter implements ViewTemplate {
    use ViewTemplateRenderer, ResourceLoader;



    protected Widget $widget;

    public function setWidget(Widget $widget): static {
        $this->widget = $widget;
        return $this;
    }
}