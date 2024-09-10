<?php

namespace modules\SideLoader;

class Css {
    public const FILE_TYPE = 'css';
    public const FILE_MIME_TYPE = 'text/css';



    public static function import(string $file): void {
        SideLoader::getInstance()
            ->import(self::FILE_TYPE, $file);
    }
}