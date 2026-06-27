<?php

namespace core\view2;

interface Container {
    public function add(View $view): static;

    public function addAll(array $views): static;
}
