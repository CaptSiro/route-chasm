<?php

namespace project;

use components\core\Html\Html;
use core\App;
use core\route\Path;
use DirectoryIterator;
use Generator;
use SplFileInfo;

class Startuh {
    public const LEXICON_GROUP = 'startuh';

    public const SETTING_BACKGROUND_DIRECTORY_OS = 'startuh:background_directory_os';
    public const SETTING_BACKGROUND_DIRECTORY_OS_HASH = 'startuh:background_directory_os_hash';
    public const HASH_ALGORITHM = 'md5';



    public static function createApi(): string {
        $request = App::getInstance()
            ->getRequest();

        return Html::wrapUnsafe(
            'script',
            json_encode([
                'randomBackground' => $request->getDomain()
                    ->createUrl(Path::from('/random-background'))
                    ->toString()
            ]),
            [
                'type' => 'application/json',
                'id' => 'api-startuh'
            ]
        );
    }

    /**
     * @param string $paths
     * @return Generator<SplFileInfo>
     */
    public static function listBackgroundFiles(string $paths): Generator {
        foreach (explode(";", $paths) as $dir) {
            if (!file_exists($dir)) {
                continue;
            }

            foreach (new DirectoryIterator($dir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                yield $fileInfo;
            }
        }
    }

    public static function backgroundsExist(string $paths): bool {
        foreach (explode(";", $paths) as $dir) {
            if (!file_exists($dir)) {
                return false;
            }
        }

        return true;
    }
}