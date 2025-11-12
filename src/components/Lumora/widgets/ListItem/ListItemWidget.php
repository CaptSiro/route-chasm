<?php

namespace components\Lumora\widgets\ListItem;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class ListItemWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WListItem";
    }

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