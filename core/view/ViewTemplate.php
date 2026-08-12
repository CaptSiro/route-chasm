<?php

namespace core\view;

interface ViewTemplate extends View {
    public function getTemplate(string $extension = '.phtml'): string;

    public function getTemplateVariant(?string $variant = null, string $extension = '.phtml'): string;

    public function renderTemplated(?string $template = null): string;
}