<?php

namespace core;

use core\utils\Objects;

trait ResourceLoader {
    private static function getClassResource(string $class, string $path = ''): string {
        return App::getInstance()
            ->getSource(dirname($class) ."/$path");
    }

    public static function getStaticResource(string $path = ''): string {
        return static::getClassResource(static::class, $path);
    }

    public static function getTemplateResourceStatic(): string {
        return self::getClassResource(static::class, basename(static::class) . '.phtml');
    }


    public static function getSelfResource(string $path = ''): string {
        return static::getClassResource(self::class, $path);
    }

    public static function getTemplateResourceSelf(): string {
        return self::getClassResource(self::class, basename(self::class) . '.phtml');
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
        return Objects::getClass($this);
    }
}