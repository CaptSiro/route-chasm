<?php

namespace components\pages\Listing;

use components\layout\Pagination\Pagination;
use components\layout\Pagination\PaginationControl;
use components\layout\Pagination\PaginationFactory;
use core\RouteChasmEnvironment;
use core\sideloader\importers\Css\Css;
use core\view\Component;
use core\view\View;
use models\Page\Page;
use models\Page\PageLocalization;

class Listing extends Component {
    public static function importAssets(): void {
        Css::import(static::getstaticResource('listing.css'));
    }



    protected PaginationFactory $factory;

    public function __construct(
        protected Page $page,
        protected PageLocalization $localization,
        int $portionSize = RouteChasmEnvironment::LISTING_PORTION_SIZE,
        Pagination&View $pagination = new PaginationControl(),
    ) {
        parent::__construct();
        $this->factory = new PaginationFactory(
            new ListingFactoryBehavior($this->page, $pagination),
            $this->page::getDescription(),
            $portionSize
        );
    }
}