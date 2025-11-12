<?php

namespace components\Lumora\widgets\Quote;

use components\core\Icon;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class QuoteWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WQuote";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-comment_quote_outline");
    }

    public function getName(): string {
        return "Quote";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("quote.js");
    }

    public function getStyles(): string {
        return $this->getResource("quote.css");
    }

    public function getDependencies(): array {
        return [
            TextEditorWidget::getInstance()
        ];
    }
}