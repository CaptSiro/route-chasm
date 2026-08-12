<?php

namespace components\nexus;

interface NexusProxyItem {
    public function isEditable(): bool;

    public function isDeletable(): bool;
}