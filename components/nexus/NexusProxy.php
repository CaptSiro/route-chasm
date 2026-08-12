<?php

namespace components\nexus;

use components\Icon;
use components\layout\Grid\Proxy\TypeProxy;
use core\Identifier;
use core\view\Html;

class NexusProxy extends TypeProxy {
    protected Nexus $context;
    protected bool $isItemIdentifier = false;
    protected bool $isNexusProxyItem = false;



    public function setItem(mixed $item): void {
        $this->isItemIdentifier = $item instanceof Identifier;
        $this->isNexusProxyItem = $item instanceof NexusProxyItem;
        parent::setItem($item);
    }

    public function setContext(Nexus $context): static {
        $this->context = $context;
        return $this;
    }

    public function getValue(string $name): string {
        if (!isset($this->context) || !$this->isItemIdentifier) {
            return match ($name) {
                Nexus::COLUMN_EDIT,
                Nexus::COLUMN_DELETE => '',
                default => parent::getValue($name)
            };
        }

        return match ($name) {
            Nexus::COLUMN_EDIT => $this->getEditValue(),
            Nexus::COLUMN_DELETE => $this->getDeleteValue(),
            default => parent::getValue($name)
        };
    }

    protected function getEditValue(): string {
        if ($this->isNexusProxyItem && !$this->item->isEditable()) {
            return '';
        }

        $id = (string) $this->item->getId();
        return $this->createEditValue(
            $this->context->getUpdateUrl((string) $this->item->getId())
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
            $this->context->getDeleteUrl((string) $this->item->getId())
        );
    }

    protected function createDeleteValue(?string $url): string {
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
            Icon::delete(),
            $attributes
        );
    }
}