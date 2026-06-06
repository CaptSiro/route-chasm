<?php

namespace components\Admin\Nexus;

interface NexusProxyItem {
    public function isEditable(): bool;

    public function isDeletable(): bool;
}