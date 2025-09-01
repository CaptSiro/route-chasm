<?php

namespace core\locale;

use core\App;

trait LocaleAutoloader {
    public static function init(): void {
        if (get_called_class() === LocaleAutoloader::class) {
            return;
        }
    }
}