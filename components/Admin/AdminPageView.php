<?php

namespace components\Admin;

use core\locale\LexiconUnit;
use core\view\PageView;
use core\view\Payload;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use core\view\View;

class AdminPageView extends PageView {
    use LexiconUnit;

    public const LEXICON_GROUP = 'admin';



    public function __construct(
        ?View $view = null,
        ?Payload $payload = null,
        ?Renderer $renderer = null
    ) {
        parent::__construct($view, $payload, $renderer);

        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}