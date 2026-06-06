<?php

namespace components\Admin\Nexus\Editor;

use components\Admin\Nexus\Editor;

trait SetEditor {
    protected Editor $editor;

    public function setEditor(Editor $editor): void {
        $this->editor = $editor;
    }
}