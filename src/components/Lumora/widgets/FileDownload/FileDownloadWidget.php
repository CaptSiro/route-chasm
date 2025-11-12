<?php

namespace components\Lumora\widgets\FileDownload;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class FileDownloadWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WFileDownload";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-oct-download");
    }

    public function getName(): string {
        return "File Download";
    }

    public function getCategory(): string {
        return "Export";
    }

    public function getScript(): string {
        return $this->getResource("file-download.js");
    }

    public function getStyles(): string {
        return $this->getResource("file-download.css");
    }

    public function getDependencies(): array {
        return [];
    }
}