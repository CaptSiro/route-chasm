<?php

namespace components\core\Admin\Nexus;

use components\core\Html\Html;
use components\core\Icon;
use components\layout\Table\Proxy\TypeProxy;
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
            AdminNexus::COLUMN_EDIT => Html::createLink(
                $this->context->getUpdateLink((string) $this->item->getId()),
                Icon::nf('nf-oct-pencil')
            ),
            AdminNexus::COLUMN_DELETE => Html::createLink(
                $this->context->getDeleteLink((string) $this->item->getId()),
                Icon::nf('nf-oct-trash')
            ),
            default => parent::getValue($name)
        };
    }
}