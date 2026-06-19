<?php

namespace components\layout\WebPage;

class AdminWebPage extends WebPage {
    public function __construct(?string $language = null, ?Head $head = null) {
        parent::__construct($language, $head);
        $this->setTemplate(
            $this->getResource('WebPage_admin.phtml')
        );
    }
}