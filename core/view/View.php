<?php

namespace core\view;

interface View {
    public function render(): string;

    public function __toString(): string;
}