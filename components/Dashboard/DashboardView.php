<?php

namespace components\Dashboard;

use components\html\HtmlHead;
use core\view\View;

interface DashboardView extends View {
    public function createDashboardHead(Dashboard $dashboard): HtmlHead;
}