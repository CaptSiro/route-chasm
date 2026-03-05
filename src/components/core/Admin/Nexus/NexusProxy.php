<?php

namespace components\core\Admin\Nexus;

use components\core\Html\Html;
use components\core\Icon;
use components\layout\Grid\Proxy\TypeProxy;
use core\Identifier;

class NexusProxy extends TypeProxy {
    protected AdminNexus $context;
    protected bool $isItemIdentifier = false;
    protected bool $isNexusProxyItem = false;



    public function setItem(mixed $item): void {
        $this->isItemIdentifier = $item instanceof Identifier;
        $this->isNexusProxyItem = $item instanceof NexusProxyItem;
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
        if ($this->isNexusProxyItem && !$this->item->isEditable()) {
            return '';
        }

        $id = (string) $this->item->getId();
        return $this->createEditValue(
            $this->context->getUpdateLink((string) $this->item->getId())
        );
    }

    protected function createEditValue(?string $url): string {
        $content = Icon::edit();
        return "<a href='$url' class='no-decoration'>$content</a>";
    }

    protected function getDeleteValue(): string {
        if ($this->isNexusProxyItem && !$this->item->isDeletable()) {
            return '';
        }

        return $this->createDeleteValue(
            $this->context->getDeleteLink((string) $this->item->getId())
        );
    }

    protected function createDeleteValue(?string $url): string {
        $content = Icon::delete();

        $attributes = [
            'class' => 'link no-decoration',
            'x-init' => 'nexus_deleteButton',
            'data-url' => $url,
        ];

        if ($this->isItemIdentifier) {
            $attributes['data-id'] = $this->item->getHumanIdentifier();
        }

        return Html::wrapUnsafe(
            'button',
            $content,
            $attributes
        );
    }
}