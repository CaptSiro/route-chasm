<?php

namespace core\pages;

use components\core\Message\Message;
use core\navigation\NavigationFactory;
use core\Singleton;
use core\view\View;

class PageFactory implements NavigationFactory {
    use Singleton;

    public function getName(): string {
        return 'page';
    }

    public function createDestination(string $data): View {
        return new Message($data);
    }
}