<?php

namespace core\pages;

use components\core\Search\SearchResult;
use components\pages\Listing\ListingCard;
use components\pages\RelatedCard;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\Page;

trait PagePreview {
    public function buildListingCard(Page $page, Language $language): View {
        return new ListingCard($page, $page->getLocalization($language));
    }

    public function buildSearchResult(Page $page, Language $language): View {
        return SearchResult::fromPage($page, $page->getLocalization($language));
    }

    public function buildRelatedCard(Page $page, Language $language): View {
        return new RelatedCard($page, $page->getLocalization($language));
    }
}