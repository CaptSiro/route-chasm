<?php

namespace core;

trait Source {
    public static function getStaticSource(string $path = ''): string {
        return App::getInstance()
            ->getSource(dirname(self::class) ."/$path");
    }



    public function getSource(string $path = ''): string {
        return App::getInstance()
            ->getSource(dirname(get_class($this)) ."/$path");
    }

    public function getClass(): string {
        return basename(get_class($this));
    }
}