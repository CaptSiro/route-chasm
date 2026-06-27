<?php

namespace core\view2;

use core\view\View;

trait ViewTemplateSlotTrait {
    /**
     * @var array<string, View>
     */
    private array $templateSlots = [];



    public function setTemplateSlot(string $slot, View $view): static {
        $this->templateSlots[$slot] = $view;
        return $this;
    }

    public function getTemplateSlot(string $slot): ?View {
        return $this->templateSlots[$slot] ?? null;
    }

    public function setTemplateSlotRenderer(Renderer $renderer): static {
        foreach ($this->templateSlots as $slot) {
            if ($slot instanceof Component) {
                $slot->setRenderer($renderer);
            }
        }

        return $this;
    }
}