<?php

namespace components\Admin;

use components\Dashboard\Dashboard;
use core\admin\AdminRouter;
use core\locale\LexiconUnit;
use core\view\Renderer;
use core\view\ViewTemplate;

class AdminProject implements ViewTemplate {
    use Renderer, LexiconUnit;

    public function __construct(
        protected ?Dashboard $dashboard = null, // todo remove ?_ = null
    ) {
        $this->setLexiconGroup(AdminRouter::LEXICON_GROUP);
    }
}