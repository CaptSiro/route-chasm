<?php

namespace modules\SideLoader;

class Javascript {
    public const FILE_TYPE = 'js';
    public const FILE_MIME_TYPE = 'text/javascript';



    public static function import(string $file): void {
        SideLoader::getInstance()
            ->import(self::FILE_TYPE, $file);
    }
}