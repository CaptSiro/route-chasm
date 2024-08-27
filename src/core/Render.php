<?php

namespace core;

interface Render {
    function render(?string $template = null): string;
}