<?php

namespace components\Lumora\widgets\Header;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class HeaderWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WHeader";
    }

    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-dock_top");
    }

    public function getName(): string {
        return "Header";
    }

    public function getCategory(): string {
        return "Layout";
    }

    public function getScript(): string {
        return $this->getResource("header.js");
    }

    public function getStyles(): string {
        return $this->getResource("header.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}