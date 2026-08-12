<?php

namespace components\nexus;

use core\view\Html;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use core\view\ViewTemplateSlotTrait;

class NexusHeader implements ViewTemplate {
    use ViewTemplateRenderer, ViewTemplateSlotTrait;

    public const SLOT_ITEM = 'nexus-header:item';
    public const SLOT_TITLE = 'nexus-header:title';



    protected bool $showCreateAction;
    protected ?string $createActionLabel;

    public function __construct(
        protected Nexus $nexus,
        ?string $createActionLabel = null
    ) {
        $this->setCreateActionLabel($createActionLabel);
    }




    public function removeTitle(): static {
        $this->setTemplateSlot($this::SLOT_TITLE, '');
        return $this;
    }

    public function setCreateActionLabel(?string $createActionLabel): static {
        $this->createActionLabel = $createActionLabel;
        $this->showCreateAction = !is_null($this->createActionLabel);
        return $this;
    }

    public function setShowCreateAction(bool $showCreateAction): static {
        $this->showCreateAction = $showCreateAction;
        return $this;
    }

    public function getHtmlHeaderTitle(): string {
        $title = $this->getTemplateSlot($this::SLOT_TITLE)
            ?? $this->nexus->getTitle();

        return empty($title) || $title === '&nbsp;'
            ? '&nbsp;'
            : Html::escape($title);
    }
}