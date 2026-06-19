<?php

namespace core\navigation;

use core\view\Component;

interface NavigationFactory {
    public function getName(): string;

    public function createDestination(string $data): Component;
}