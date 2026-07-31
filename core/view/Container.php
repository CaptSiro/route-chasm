<?php

namespace core\view;

interface Container {
    public function add(View|string $view): static;

    public function addAll(array $views): static;
}
