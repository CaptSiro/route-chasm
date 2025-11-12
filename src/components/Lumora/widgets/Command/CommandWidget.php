<?php

namespace components\Lumora\widgets\Command;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class CommandWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WCommand";
    }

    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-oct-command_palette");
    }

    public function getName(): string {
        return "Command";
    }

    public function getCategory(): string {
        return "System";
    }

    public function getScript(): string {
        return $this->getResource("command.js");
    }

    public function getStyles(): string {
        return $this->getResource("command.css");
    }

    public function getDependencies(): array {
        return [];
    }
}