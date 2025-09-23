<?php

namespace components\pages\Wireframe;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\Head;
use core\App;
use core\view\ArrayContainer;
use core\view\Component;
use core\view\Container;
use models\core\Language\Language;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;
use RuntimeException;

class Wireframe extends Component implements Container {
    use ArrayContainer;

    public static function createHtmlHead(LocalizedPage $localization): HtmlHead {
        $head = new HtmlHead($localization->title);

        $meta = $localization->getMeta();

        $head->addMetaNonEmpty('description', $meta->description);
        $head->addMetaNonEmpty('keywords', $meta->keywords);
        $head->addMetaNonEmpty('og-title', $meta->ogTitle);
        $head->addMetaNonEmpty('og-description', $meta->ogDescription);

        return $head;
    }



    protected Language $language;
    protected Head $head;
    protected LocalizedPage $localization;

    public function __construct(
        protected Page $page,
        bool $isMiddleware = false,
        ?Language $language = null
    ) {
        parent::__construct($isMiddleware);
        $this->language = $language ?? App::getInstance()
            ->getRequest()
            ->getLanguage();

        $localization = $page->getLocalization($this->language)
            ?? $page->getLocalization(App::getDefaultLanguage());

        if (is_null($localization)) {
            throw new RuntimeException("Page is no localization found for page. Cannot display.");
        }

        $this->localization = $localization;
        $this->head = self::createHtmlHead($localization);
    }
}