<?php

namespace components\layout\Dashboard;

use core\view\PageView;
use core\view\PageViewFactory;

class DashboardPageViewFactory extends PageViewFactory {
    public function __construct(
        protected Dashboard $dashboard
    ) {}



    public function create(): PageView {
        return new DashboardPageView($this->dashboard);
    }
}