<?php

namespace components\Dashboard;

use components\layout\Menu\Menu;
use core\view\Renderer;
use core\view\TemplateSlots;
use core\view\ViewTemplate;

class DashboardSideBar implements ViewTemplate {
    use Renderer, TemplateSlots;

    public const SLOT_HEADER = 'header';

    public const SLOT_FOOTER = 'footer';



    public function __construct(
        protected Menu $menu,
    ) {}
}