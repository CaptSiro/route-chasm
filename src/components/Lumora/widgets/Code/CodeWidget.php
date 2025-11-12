<?php

namespace components\Lumora\widgets\Code;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class CodeWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WCode";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-fa-code");
    }

    public function getName(): string {
        return "Code";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("code.js");
    }

    public function getStyles(): string {
        return $this->getResource("code.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}