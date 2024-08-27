<?php

namespace core;

trait Source {
    public function getSource(string $path): string {
        return App::getInstance()
            ->getSource(dirname(get_class($this)) ."/$path");
    }
}