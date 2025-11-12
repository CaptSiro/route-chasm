<?php

namespace components\Lumora\widgets\Image;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class ImageWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WImage";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-fa-image");
    }

    public function getName(): string {
        return "Image";
    }

    public function getCategory(): string {
        return "Visual";
    }

    public function getScript(): string {
        return $this->getResource("image.js");
    }

    public function getStyles(): string {
        return $this->getResource("image.css");
    }

    public function getDependencies(): array {
        return [];
    }
}