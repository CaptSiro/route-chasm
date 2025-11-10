<?php

namespace components\widgets\Code;

use components\core\Icon;
use components\widgets\TextEditor\TextEditorWidget;
use components\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class CodeWidget implements Widget {
    use Singleton, ResourceLoader;



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