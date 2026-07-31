<?php

namespace core\view;

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

    protected function setTemplateSlotRenderer(Renderer $renderer): static {
        foreach ($this->templateSlots as $slot) {
            Component::propagateSetRenderer($slot, $renderer);
        }

        return $this;
    }
}