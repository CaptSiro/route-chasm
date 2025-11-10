<?php

namespace components\widgets\TextEditor;

use components\core\Icon;
use components\widgets\Decoration\TextDecorationWidget;
use components\widgets\Link\LinkWidget;
use components\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class TextEditorWidget implements Widget {
    use Singleton, ResourceLoader;



    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-cod-edit");
    }

    public function getName(): string {
        return "Text Editor";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("text-editor.js");
    }

    public function getStyles(): string {
        return $this->getResource("text-editor.css");
    }

    public function getDependencies(): array {
        return [
            TextDecorationWidget::getInstance(),
            LinkWidget::getInstance()
        ];
    }
}