<?php

namespace components\core\Admin\Project;

use core\admin\AdminRouter;
use core\locale\LexiconUnit;
use core\view\Renderer;
use core\view\View;

class AdminProject implements View   {
    use Renderer, LexiconUnit;

    public function __construct() {
        $this->setLexiconGroup(AdminRouter::LEXICON_GROUP);
    }
}