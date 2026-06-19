<?php

namespace core\view;

trait TemplateSlots {
    /**
     * @var array<string, View>
     */
    private array $templateSlots;



    public function setTemplateSlot(string $slot, View $view): static {
        $this->templateSlots[$slot] = $view;
        return $this;
    }

    public function getTemplateSlot(string $slot): ?View {
        return $this->templateSlots[$slot] ?? null;
    }
}