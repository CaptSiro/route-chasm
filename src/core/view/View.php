<?php

namespace core\view;

interface View {
    public function render(?string $template = null): string;

    public function getRoot(): View;
}