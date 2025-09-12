<?php

namespace core\pages;

use core\view\View;

interface PageTemplate {
    public function getName(): string;

    public function create(Page $page): View;
}