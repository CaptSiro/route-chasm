<?php

namespace models\core\Page\Grid;

use components\layout\Grid\Grid;
use components\layout\Grid\Loader\GridLoader;
use core\App;

class PageGridLoader implements GridLoader {
    public function load(Grid $context): array {
        $request = App::getInstance()->getRequest();
        $parent = $request->getUrl()->getQuery()->get('parent');

        return PageGridRow::children(
            $request->getLanguage(),
            is_null($parent)
                ? null
                : intval($parent)
        );
    }
}