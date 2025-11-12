<?php

namespace components\Lumora\widgets\Root;

use components\core\Icon;
use components\Lumora\widgets\CommentSection\CommentSectionWidget;
use components\Lumora\widgets\Header\HeaderWidget;
use components\Lumora\widgets\Page\PageWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class RootWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WRoot";
    }

    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-file_cog_outline");
    }

    public function getName(): string {
        return "Website Settings";
    }

    public function getCategory(): string {
        return "System";
    }

    public function getScript(): string {
        return $this->getResource("root.js");
    }

    public function getStyles(): string {
        return $this->getResource("root.css");
    }

    public function getDependencies(): array {
        return [
            HeaderWidget::getInstance(),
            PageWidget::getInstance(),
            CommentSectionWidget::getInstance()
        ];
    }
}