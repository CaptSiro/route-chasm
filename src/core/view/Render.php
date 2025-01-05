<?php

namespace core\view;

interface Render {
    function render(?string $template = null): string;
}