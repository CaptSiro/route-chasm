<?php

namespace components\layout;

use core\view\View;

interface Layout {
    public function add(View $child): static;
}