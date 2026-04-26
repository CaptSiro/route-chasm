<?php

namespace components\pages;

use core\fs\variants\ImageVariant;
use core\locale\LexiconUnit;
use core\url\Url;
use core\view\Renderer;
use core\view\View;
use models\core\fs\File;
use models\core\Page\PageLocalization;
use models\core\Page\Page;

class RelatedCard implements View {
    use Renderer, LexiconUnit;

    public const LEXICON_GROUP = 'related.card';
    public const TRANSFORMER_RELATED = ImageVariant::TRANSFORMER_RELATED;



    public function __construct(
        protected Page $page,
        protected PageLocalization $localization
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }


    protected function getImageUrl(?File $image): ?Url {
        if (is_null($image)) {
            return null;
        }

        return $image->getUrl(
            ImageVariant::resolve(
                self::TRANSFORMER_RELATED,
                80, 80
            )
        );
    }
}