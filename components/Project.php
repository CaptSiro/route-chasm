<?php

namespace components;

use core\locale\LexiconUnit;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class Project implements ViewTemplate {
    use ViewTemplateRenderer, LexiconUnit;

    public const LEXICON_GROUP = 'project';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}