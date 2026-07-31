<?php

namespace core\view_old;

interface Container extends View {
    public function addContent(View $view): static;
}