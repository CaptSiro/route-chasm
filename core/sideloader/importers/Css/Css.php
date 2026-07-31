<?php

namespace core\sideloader\importers\Css;

use core\sideloader\FileImporter;
use core\sideloader\Importer;
use core\sideloader\SideLoader;
use core\view\View;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class Css implements Importer, View {
    use ViewTemplateRenderer, FileImporter;

    public const FILE_EXTENSION = 'css';
    public const FILE_MIME_TYPE = 'text/css';



    public static function import(string $file): void {
        SideLoader::getInstance()
            ->import(self::FILE_EXTENSION, $file);
    }

    public static function importDefault(ViewTemplate $view): void {
        self::import($view->getResource($view->getClass() . '.css'));
    }



    public function getFileExtension(): string {
        return self::FILE_EXTENSION;
    }

    public function getFileMimeType(): string {
        return self::FILE_MIME_TYPE;
    }

    public function fileHead(string $file): string {
        return PHP_EOL."/* FILE ". basename($file) ." */".PHP_EOL;
    }

    public function begin(): ?string {
        return null;
    }

    public function end(): ?string {
        return null;
    }
}