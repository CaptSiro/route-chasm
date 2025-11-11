<?php

namespace core\sideloader\importers\Javascript;

use core\sideloader\FileImporter;
use core\sideloader\Importer;
use core\sideloader\SideLoader;
use core\view\Renderer;
use core\view\View;

class Javascript implements Importer, View {
    use Renderer, FileImporter;

    public const FILE_EXTENSION = 'js';
    public const FILE_MIME_TYPE = 'text/javascript';



    public static function import(string $file): void {
        SideLoader::getInstance()
            ->import(self::FILE_EXTENSION, $file);
    }



    public function getFileExtension(): string {
        return self::FILE_EXTENSION;
    }

    public function getFileMimeType(): string {
        return self::FILE_MIME_TYPE;
    }

    public function fileHead(string $file): ?string {
        return PHP_EOL."// FILE ". basename($file) .PHP_EOL;
    }

    public function begin(): ?string {
        return null;
    }

    public function end(): ?string {
        return "window.dispatchEvent(new CustomEvent('scriptLoad'));";
    }
}