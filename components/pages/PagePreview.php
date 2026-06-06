<?php

namespace components\pages;

use components\pages\Listing\ListingCard;
use components\Search\SearchResult;
use core\view\View;
use models\Language\Language;
use models\Page\Page;

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