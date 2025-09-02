<?php

namespace components\core\Admin\Nexus;

interface NexusProxyItem {
    public function isEditable(): bool;

    public function isDeletable(): bool;
}