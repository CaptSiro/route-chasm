<?php

namespace components\Modules;

use components\layout\Grid\Grid;
use core\App;
use core\locale\LexiconUnit;
use core\view\ContainerContent;
use core\view\View;

class Modules extends ContainerContent {
    public const LEXICON_GROUP = 'admin.modules';



    public function __construct() {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }

    public function getModules(): View {
        $table = new Grid(proxy: new ModulesProxy());
        $table
            ->add('name', $this->tr('Name'), '128px')
            ->add('identifier', $this->tr('Identifier'))
            ->add('version', $this->tr('Version'), '64px');

        $table->load(
            App::getInstance()->getLoadedModules()
        );

        return $table;
    }
}