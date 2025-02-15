<?php

use components\core\Admin\Editor\AdminEditor;
use components\core\Admin\MenuV2\AdminMenuV2;
use components\core\Modules\Modules;
use entities\core\Domain;

AdminMenuV2::getInstance()
    ->add('/Modules', new Modules())
    ->add('/Domains', new AdminEditor(Domain::class))
;
