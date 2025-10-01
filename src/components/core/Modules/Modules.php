<?php

namespace components\core\Modules;

use components\layout\Grid\Grid;
use core\App;
use core\view\ContainerContent;
use core\view\View;

class Modules extends ContainerContent {
    public function __construct() {
        parent::__construct();
    }

    public function getModules(): View {
        $table = new Grid(proxy: new ModulesProxy());
        $table
            ->add('name', 'Name', '128px')
            ->add('identifier', 'Identifier')
            ->add('version', 'Version', '64px');

        $table->load(
            App::getInstance()->getLoadedModules()
        );

        return $table;
    }
}