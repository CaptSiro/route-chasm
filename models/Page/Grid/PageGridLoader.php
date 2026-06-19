<?php

namespace models\Page\Grid;

use components\layout\Grid\Loader\ModelGridLoader;
use components\layout\Pagination\Pagination;
use components\layout\Pagination\PaginationControl;
use core\App;
use core\communication\Request;
use core\database\sql\ModelFactory;
use core\database\sql\query\SelectQuery;
use core\RouteChasmEnvironment;

class PageGridLoader extends ModelGridLoader {
    public static function getParentId(Request $request): ?int {
        $parent = $request->getUrl()->getQuery()->get('parent');
        return empty($parent)
            ? null
            : intval($parent);
    }

    public static function getLanguageId(Request $request): int {
        return $request->getLanguage()->id;
    }



    public function __construct(
        bool $paginate = true,
        int $portionSize = RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE,
        Pagination $pagination = new PaginationControl()
    ) {
        parent::__construct(
            PageGridRow::class,
            $paginate,
            $portionSize,
            $pagination
        );
    }



    protected function createSelectQuery(ModelFactory $factory): SelectQuery {
        $request = App::getInstance()->getRequest();

        return PageGridRow::childrenQuery(
            self::getLanguageId($request),
            self::getParentId($request)
        );
    }

    protected function getCount(ModelFactory $factory): int {
        $request = App::getInstance()->getRequest();

        return PageGridRow::phrasesCount(
            self::getLanguageId($request),
            self::getParentId($request)
        );
    }
}