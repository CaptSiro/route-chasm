<?php

namespace components\pages\Wireframe;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\Head;
use core\actions\Action;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\view\ArrayContainer;
use core\view\Component;
use core\view\Container;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;
use models\core\Privilege\Privilege;
use models\core\User\User;
use RuntimeException;

class Wireframe extends Component implements Container {
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
    protected HtmlHead $head;
    protected LocalizedPage $localization;
    protected View $content;
    protected ?Action $action;

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
            throw new RuntimeException("No localization found for page. Cannot display.");
        }

        $this->localization = $localization;
        $this->head = self::createHtmlHead($localization);
    }



    public function getHead(): HtmlHead {
        return $this->head;
    }

    public function getLocalization(): LocalizedPage {
        return $this->localization;
    }

    public function addContent(View $view): static {
        $this->content = $view;

        if ($view instanceof Action) {
            $this->action = $view;
        }

        return $this;
    }

    public function hasContentAccess(): bool {
        return $this->page->hasAccess(
            User::fromRequest(App::getInstance()->getRequest()),
            Privilege::fromName(Privilege::READ)
        );
    }

    public function perform(Request $request, Response $response): void {
        if (!is_null($this->action)) {
            $this->action->perform($request, $response);
            return;
        }

        parent::perform($request, $response);
    }
}