<?php

namespace core\view2;

interface View {
    public function render(): string;

    public function __toString(): string;
}