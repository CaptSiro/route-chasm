<?php

namespace components\pages\Listing;

use core\fs\variants\ImageVariant;
use core\locale\LexiconUnit;
use core\url\Url;
use core\view\Renderer;
use core\view\View;
use models\core\fs\File;
use models\core\Page\PageLocalization;
use models\core\Page\Page;

class ListingCard implements View {
    use Renderer, LexiconUnit;


    public const LEXICON_GROUP = 'listing.card';

    public const TRANSFORMER_CARD_SMALL = ImageVariant::TRANSFORMER_LISTING_CARD_SMALL;

    public const TRANSFORMER_CARD_MEDIUM = ImageVariant::TRANSFORMER_LISTING_CARD_MEDIUM;

    public const TRANSFORMER_CARD_LARGE = ImageVariant::TRANSFORMER_LISTING_CARD_LARGE;



    public function __construct(
        protected Page $page,
        protected PageLocalization $localization
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    protected function getImageUrlSmall(?File $image): ?Url {
        if (is_null($image)) {
            return null;
        }

        return $image->getUrl(
            ImageVariant::resolve(
                self::TRANSFORMER_CARD_SMALL,
                480, 270
            )
        );
    }

    protected function getImageUrlMedium(?File $image): ?Url {
        if (is_null($image)) {
            return null;
        }

        return $image->getUrl(
            ImageVariant::resolve(
                self::TRANSFORMER_CARD_MEDIUM,
                800, 450
            )
        );
    }

    protected function getImageUrlLarge(?File $image): ?Url {
        if (is_null($image)) {
            return null;
        }

        return $image->getUrl(
            ImageVariant::resolve(
                self::TRANSFORMER_CARD_LARGE,
                1200, 675
            )
        );
    }
}