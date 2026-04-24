<?php

namespace components\pages\Article;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Search\SearchResult;
use components\pages\Listing\ListingCard;
use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\fs\variants\FileVariantTransformer;
use core\fs\variants\ImageVariant;
use core\pages\PageTemplate;
use core\view\Component;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\Page;

class ArticleTemplate implements PageTemplate {
    public const DATA_CONTENT = 'article.md';
    public const TRANSFORMER_ARTICLE_COVER = 'article-cover';

    public static function getCoverTransformer(): FileVariantTransformer {
        return ImageVariant::resolve(
            self::TRANSFORMER_ARTICLE_COVER,
            900, 500,
        );
    }



    public function getName(): string {
        return "Article";
    }

    public function getDescription(): string {
        return "Generic text page generated from Markdown code. Includes multilingual Markdown editor";
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function delete(Page $page): ?View {
        foreach ($page->getLocalizations() as $localization) {
            $localization
                ->get(self::DATA_CONTENT)
                ->delete();
        }

        return null;
    }

    public function buildContent(Wireframe $wireframe, Page $page): Component {
        return new Article(
            $page,
            $wireframe->getLocalization(),
            $wireframe->getLocalization()
                ->get(self::DATA_CONTENT)
                ->read() ?? ''
        );
    }

    public function buildListingCard(Page $page, Language $language): View {
        return new ListingCard($page, $page->getLocalization($language));
    }

    public function buildSearchResult(Page $page, Language $language): View {
        return SearchResult::fromPage($page, $page->getLocalization($language));
    }

    public function hasEditor(): bool {
        return true;
    }

    public function buildEditor(Page $page): Action {
        return new ArticleEditor($page);
    }

    public function buildEditorBehavior(): ?EditorBehavior {
        return null;
    }
}