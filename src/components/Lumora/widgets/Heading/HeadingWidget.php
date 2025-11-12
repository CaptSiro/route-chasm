<?php

namespace components\Lumora\widgets\Heading;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class HeadingWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WHeading";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-fa-heading");
    }

    public function getName(): string {
        return "Heading";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("heading.js");
    }

    public function getStyles(): string {
        return $this->getResource("heading.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}