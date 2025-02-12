<?php

namespace core\view;

interface Container extends Render {
    public function addContent(Render $render): static;
}