<?php

namespace components\pages;

use components\html\HtmlHead;
use components\Icon;
use components\layout\BreadCrumbs\BreadCrumbs;
use core\actions\Action;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\view\Component;
use core\view\Container;
use core\view\Html;
use core\view\StringRenderer;
use core\view\View;
use DateTime;
use models\Language\Language;
use models\Page\Page;
use models\Page\PageLocalization;
use models\Privilege\Privilege;
use models\User\User;
use RuntimeException;

class Wireframe extends Component implements Container {
    public const LEXICON_GROUP = 'page';



    public static function createHtmlHead(PageLocalization $localization): HtmlHead {
        $head = new HtmlHead(Html::escape($localization->title));

        if (!is_null($meta = $localization->getMeta())) {
            $head->addMetaNonEmpty('description', Html::escape($meta->description));
            $head->addMetaNonEmpty('keywords', Html::escape($meta->keywords));
            $head->addMetaNonEmpty('og-title', Html::escape($meta->ogTitle));
            $head->addMetaNonEmpty('og-description', Html::escape($meta->ogDescription));
        }

        $head->addElement(new StringRenderer(self::createLocalizationApi($localization)));

        return $head;
    }

    public static function createLocalizationApi(PageLocalization $localization): string {
        $releaseDate = new DateTime($localization->getPage()->getReleaseDate());

        $localizations = [];
        $page = $localization->getPage();
        foreach ($page->getLocalizations() as $l) {
            $language = $l->getLanguage();
            $localizations[] = [
                'language' => $language->getLocale()
                    ->getName(),
                'code' => $language->code,
                'url' => $page->getUrl($language)
            ];
        }

        $api = [
            'language' => App::getInstance()
                ->getRequest()
                ->getLanguage()
                ->getLocale()
                ->getName(),
            'title' => $localization->title,
            'releaseDate' => $localization
                ->getLanguage()
                ->getLocale()
                ->formatDateTime($releaseDate->getTimestamp()),
            'localizations' => $localizations
        ];

        if (!is_null($meta = $localization->getMeta())) {
            $api['description'] = $meta->description;
        }

        return Html::wrapUnsafe(
            'script',
            json_encode($api),
            [
                'type' => 'application/json',
                'id' => 'api-localization'
            ]
        );
    }

    public static function createBreadCrumbs(Page $page): array {
        $request = App::getInstance()->getRequest();

        $url = $request->getDomain()->createUrl()
            ->setQuery($request->getUrl()->getQuery());

        $breadCrumbs = [
            $url->toString() => Icon::home()
        ];

        $language = $request->getLanguage();

        foreach ($page->getParents() as $parent) {
            $breadCrumbs[$parent->getUrl()->toString()] = $parent->getLocalizationOrDefault($language)->title;
        }

        $breadCrumbs[] = $page->getLocalizationOrDefault($language)->title;
        return $breadCrumbs;
    }



    protected Language $language;
    protected HtmlHead $head;
    protected PageLocalization $localization;
    protected View $content;
    protected ?Action $action;
    protected bool $doAddHeader = true;
    protected bool $doAddFooter = true;
    protected bool $doAddBreadCrumbs = true;

    public function __construct(
        protected Page $page,
        bool $isMiddleware = false,
        ?Language $language = null
    ) {
        parent::__construct($isMiddleware);
        $this->setLexiconGroup(self::LEXICON_GROUP);

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

    public function getBreadCrumbs(): BreadCrumbs {
        return BreadCrumbs::from(static::createBreadCrumbs($this->page))
            ->setDelimitor(null);
    }

    public function setDoAddHeader(bool $doAddHeader): void {
        $this->doAddHeader = $doAddHeader;
    }

    public function setDoAddBreadCrumbs(bool $doAddBreadCrumbs): void {
        $this->doAddBreadCrumbs = $doAddBreadCrumbs;
    }

    public function setDoAddFooter(bool $doAddFooter): void {
        $this->doAddFooter = $doAddFooter;
    }

    public function getLocalization(): PageLocalization {
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