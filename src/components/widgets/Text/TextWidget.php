<?php

namespace components\widgets\Text;

use components\core\Icon;
use components\widgets\TextEditor\TextEditorWidget;
use components\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class TextWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WText";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-text");
    }

    public function getName(): string {
        return "Text";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("text.js");
    }

    public function getStyles(): string {
        return $this->getResource("text.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}