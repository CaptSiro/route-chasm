<?php

namespace components\Admin;

use core\locale\LexiconUnit;
use core\ResourceLoader;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use models\fs\Directory;

class FileSystemCreateDirectory implements ViewTemplate {
    use ViewTemplateRenderer, ResourceLoader, LexiconUnit;

    public const LEXICON_GROUP = 'admin.fs';



    public function __construct(
        protected Directory $directory
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}