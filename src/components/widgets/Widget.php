<?php

namespace components\widgets;

interface Widget {
    public function getName(): string;

    public function getCategory(): string;

    public function getScript(): string;

    public function getIcon(): string;

    public function getStyles(): string;
}