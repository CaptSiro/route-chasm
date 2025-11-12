<?php

namespace components\Lumora\widgets\CommentSection;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class CommentSectionWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WCommentSection";
    }

    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-fa-comments");
    }

    public function getName(): string {
        return "Comment Section";
    }

    public function getCategory(): string {
        return "Layout";
    }

    public function getScript(): string {
        return $this->getResource("comment-section.js");
    }

    public function getStyles(): string {
        return $this->getResource("comment-section.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}