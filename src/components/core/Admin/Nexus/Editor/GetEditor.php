<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\Editor;

trait GetEditor {
    public static function getEditor(): Editor {
        return new Editor\AdminNexusEditor(
            new static()
        );
    }
}