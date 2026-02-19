<?php

namespace components\core\fs\FileContent;

use core\view\Component;

class FileContentEditor extends Component {
    /**
     * @param array<string, FileContent> $files
     */
    public function __construct(
        protected array $files,
        protected bool $readonly = false
    ) {
        parent::__construct();
    }
}