<?php

namespace core\navigation;

use core\view\View;

interface NavigationFactory {
    public function getName(): string;

    public function createDestination(string $data): View;
}