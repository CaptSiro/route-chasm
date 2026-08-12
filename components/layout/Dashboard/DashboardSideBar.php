<?php

namespace components\layout\Dashboard;

use components\layout\Menu\Menu;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use core\view\ViewTemplateSlotTrait;

class DashboardSideBar implements ViewTemplate {
    use ViewTemplateRenderer, ViewTemplateSlotTrait;

    public const SLOT_HEADER = 'header';

    public const SLOT_FOOTER = 'footer';



    public function __construct(
        protected Menu $menu,
    ) {}
}