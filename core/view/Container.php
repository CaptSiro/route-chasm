<?php

namespace core\view;

interface Container extends View {
    public function addContent(View $view): static;
}