<?php

namespace components\Admin;

use core\locale\LexiconUnit;
use core\ResourceLoader;
use core\view\Renderer;
use core\view\ViewTemplate;
use models\fs\Directory;

class AdminFileSystemCreateDirectory implements ViewTemplate {
    use Renderer, ResourceLoader, LexiconUnit;

    public const LEXICON_GROUP = 'admin.fs';



    public function __construct(
        protected Directory $directory
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}