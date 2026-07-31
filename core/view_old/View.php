<?php

namespace core\view_old;

interface View {
    public function render(): string;

    public function getRoot(): View;

    public function __toString(): string;
}