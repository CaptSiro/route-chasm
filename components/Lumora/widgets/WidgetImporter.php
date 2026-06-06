<?php

namespace components\Lumora\widgets;

use core\ResourceLoader;
use core\view\Renderer;
use core\view\View;

class WidgetImporter implements View {
    use Renderer, ResourceLoader;



    protected Widget $widget;

    public function setWidget(Widget $widget): static {
        $this->widget = $widget;
        return $this;
    }
}