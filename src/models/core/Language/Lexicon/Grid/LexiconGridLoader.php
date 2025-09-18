<?php

namespace models\core\Language\Lexicon\Grid;

use components\layout\Grid\Grid;
use components\layout\Grid\Loader\GridLoader;

class LexiconGridLoader implements GridLoader {
    public function load(Grid $context): array {
        return LexiconGridRow::phrases();
    }
}