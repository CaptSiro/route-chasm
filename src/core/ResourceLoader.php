<?php

namespace core;

trait ResourceLoader {
    public static function getClassResource(string $path = ''): string {
        return App::getInstance()
            ->getSource(dirname(self::class) ."/$path");
    }



    public function getResource(string $path = ''): string {
        return App::getInstance()
            ->getSource(dirname(get_class($this)) ."/$path");
    }

    public function getResources(string $directory = ''): array {
        $dir = $this->getResource($directory);
        $sources = [];

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sources[] = $dir .'/'. $file;
        }

        return $sources;
    }

    public function getClass(): string {
        return basename(get_class($this));
    }
}