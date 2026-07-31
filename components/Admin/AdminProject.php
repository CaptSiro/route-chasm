<?php

namespace components\Admin;

use components\Dashboard\Dashboard;
use core\admin\AdminRouter;
use core\locale\LexiconUnit;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class AdminProject implements ViewTemplate {
    use ViewTemplateRenderer, LexiconUnit;



    public function __construct(
        protected ?Dashboard $dashboard = null, // todo remove ?_ = null
    ) {
        $this->setLexiconGroup(AdminRouter::LEXICON_GROUP);
    }
}