<?php

namespace components\pages\Listing;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Message\Message;
use components\core\Search\SearchResult;
use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\pages\PageTemplate;
use core\RouteChasmEnvironment;
use core\view\Component;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\Page;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class ListingTemplate implements PageTemplate {
    public const DATA_CONTENT = 'article.md';
    public const NAME_PORTION_SIZE = 'route-chasm-core:number_of_articles_per_listing_page';



    public function getName(): string {
        return "Page Listing";
    }

    public function getDescription(): string {
        return "Lists all direct children pages that are available for the user to see";
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function delete(Page $page): ?View {
        return null;
    }

    public function buildContent(Wireframe $wireframe, Page $page): Component {
        $portionSize = Setting::fromName(
            self::NAME_PORTION_SIZE,
            true,
            RouteChasmEnvironment::LISTING_PORTION_SIZE,
            [PROPERTY_EDITABLE => true]
        );

        return new Listing(
            $page,
            $wireframe->getLocalization(),
            $portionSize->toInt()
        );
    }

    public function buildListingCard(Page $page, Language $language): View {
        return new ListingCard($page, $page->getLocalization($language));
    }

    public function buildSearchResult(Page $page, Language $language): View {
        return SearchResult::fromPage($page, $page->getLocalization($language));
    }

    public function hasEditor(): bool {
        return false;
    }

    public function buildEditor(Page $page): Action {
        return new Message('Page Listing has no content editor associated with its template');
    }

    public function buildEditorBehavior(): ?EditorBehavior {
        return null;
    }
}