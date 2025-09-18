<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\Editor;

trait SetEditor {
    protected Editor $editor;

    public function setEditor(Editor $editor): void {
        $this->editor = $editor;
    }
}