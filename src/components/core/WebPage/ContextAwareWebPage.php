<?php

namespace components\core\WebPage;

use core\admin\AdminRouter;
use core\App;

class ContextAwareWebPage extends WebPage {
    private string $templateOverride;

    public function __construct(?string $language = null, ?Head $head = null) {
        parent::__construct($language, $head);
    }



    public function overrideTemplate(string $template): void {
        $this->templateOverride = $template;
    }

    public function render(): string {
        if (AdminRouter::isAdmin(App::getInstance()->getRequest())) {
            if (isset($this->templateOverride)) {
                $this->setTemplate($this->templateOverride);
            } else {
                $this->setTemplate($this->getResource('WebPage.admin.phtml'));
            }
        }

        return parent::render();
    }
}