<?php

namespace components;

use components\html\HtmlHead;
use components\layout\WebPage\ContextAwareWebPage;
use core\view\ContainerContent;

class NotFound extends ContainerContent {
    public const LEXICON_GROUP = 'not-found';



    public function __construct(
        protected string $title
    ) {
        parent::__construct(new ContextAwareWebPage(head: $head = new HtmlHead()));

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $head->setTitle($title);
    }

    public function createTitle(): string {
        return $this->tr('Page not found') . ': ' . $this->title;
    }
}