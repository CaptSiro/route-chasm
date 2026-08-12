<?php

namespace components\pages\Article;

use components\nexus\NexusEditorBehavior;
use components\pages\PagePreview;
use components\pages\PageTemplate;
use components\pages\Wireframe;
use core\actions\Action;
use core\fs\variants\FileVariantTransformer;
use core\fs\variants\ImageVariant;
use core\view\Component;
use core\view\View;
use models\Language\Language;
use models\Page\Page;

class ArticleTemplate implements PageTemplate {
    use PagePreview;

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

    public function hasEditor(): bool {
        return true;
    }

    public function buildEditor(Page $page): Action {
        return new ArticleEditor($page);
    }

    public function buildEditorBehavior(): ?NexusEditorBehavior {
        return null;
    }

    public function buildContent(Page $page, Language $language): Component {
        $localization = Wireframe::getLocalization($page, $language);

        return new Article(
            $page,
            $localization,
            $localization
                ->get(self::DATA_CONTENT)
                ->read() ?? ''
        );
    }
}