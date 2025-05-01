<?php

namespace components\core\Admin\Nexus;

use components\core\Icon;
use components\layout\Grid\Proxy\TypeProxy;
use core\Identifier;

class NexusProxy extends TypeProxy {
    protected AdminNexus $context;
    protected bool $isItemIdentifier = false;



    public function setItem(mixed $item): void {
        $this->isItemIdentifier = $item instanceof Identifier;
        parent::setItem($item);
    }

    public function setContext(AdminNexus $context): static {
        $this->context = $context;
        return $this;
    }

    public function getValue(string $name): string {
        if (!isset($this->context) || !$this->isItemIdentifier) {
            return match ($name) {
                AdminNexus::COLUMN_EDIT,
                AdminNexus::COLUMN_DELETE => '',
                default => parent::getValue($name)
            };
        }

        return match ($name) {
            AdminNexus::COLUMN_EDIT => $this->getEditValue(),
            AdminNexus::COLUMN_DELETE => $this->getDeleteValue(),
            default => parent::getValue($name)
        };
    }

    protected function getEditValue(): string {
        $url = $this->context->getUpdateLink((string) $this->item->getId());
        $content = Icon::nf('nf-oct-pencil', 'Edit');
        return "<a href='$url' class='link no-style'>$content</a>";
    }

    protected function getDeleteValue(): string {
        $url = $this->context->getDeleteLink((string) $this->item->getId());
        $content = Icon::nf('nf-oct-trash', 'Delete');
        return "<button class='link no-style' x-init='nexus_deleteButton' data-url='$url'>$content</button>";
    }
}