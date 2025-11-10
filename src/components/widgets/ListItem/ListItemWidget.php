<?php

namespace components\widgets\ListItem;

use components\core\Icon;
use components\widgets\TextEditor\TextEditorWidget;
use components\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class ListItemWidget implements Widget {
    use Singleton, ResourceLoader;



    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-cod-circle");
    }

    public function getName(): string {
        return "List Item";
    }

    public function getCategory(): string {
        return "Layout";
    }

    public function getScript(): string {
        return $this->getResource("list-item.js");
    }

    public function getStyles(): string {
        return $this->getResource("list-item.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}