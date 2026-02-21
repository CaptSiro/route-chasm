<?php

namespace components\Lumora\widgets\Html;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class HtmlWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WHtml";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-fa-html5");
    }

    public function getName(): string {
        return "Html";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("html.js");
    }

    public function getStyles(): string {
        return $this->getResource("html.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}