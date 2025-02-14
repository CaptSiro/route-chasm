<?php

namespace components\core\Modules;

use components\layout\Table\Table;
use core\App;
use core\view\ContainerContent;
use core\view\View;

class Modules extends ContainerContent {
    public function __construct() {
        parent::__construct();
    }

    public function getModules(): View {
        $table = new Table(new ModulesProxy());
        $table
            ->add('Name', 'name')
            ->add('Identifier', 'identifier')
            ->add('Version', 'version');

        $table->load(
            App::getInstance()->getLoadedModules()
        );

        return $table;
    }
}