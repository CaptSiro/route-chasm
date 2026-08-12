<?php

namespace components\layout;

use core\view\ContainerTrait;

trait DynamicContainer {
    use ContainerTrait;

    protected float $widthPercentage;



    public function getStyle(): string {
        return 'style="width: ' .($this->widthPercentage * 100). '%;"';
    }
}