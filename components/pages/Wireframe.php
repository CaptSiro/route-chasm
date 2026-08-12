<?php

namespace components\pages;

use components\Icon;
use components\layout\BreadCrumbs\BreadCrumbs;
use core\App;
use core\Deprecated;
use core\locale\LexiconUnit;
use core\view\Component;
use core\view\Head;
use core\view\Html;
use core\view\PageView;
use core\view\Payload;
use core\view\Renderer;
use core\view\View;
use DateTime;
use models\Language\Language;
use models\Page\Page;
use models\Page\PageLocalization;
use models\Privilege\Privilege;
use models\User\User;
use RuntimeException;

class Wireframe extends PageView {
    use LexiconUnit;

    public const PAYLOAD_DO_ADD_HEADER = 'wireframe:do-add-header';
    public const PAYLOAD_DO_ADD_FOOTER = 'wireframe:do-add-footer';
    public const PAYLOAD_DO_ADD_BREAD_CRUMBS = 'wireframe:do-add-bread-crumbs';

    public const LEXICON_GROUP = 'page';



    /**
     * @param View $view
     * @param string $title
     * @return static
     * @deprecated
     */
    public static function from(View $view, string $title): static {
        throw new Deprecated();
    }

    /**
     * @param Component $component
     * @return static
     * @deprecated
     */
    public static function fromComponent(Component $component): static {
        throw new Deprecated();
    }

    public static function fromTemplate(PageTemplate $template, Page $page, Language $language): static {
        $component = $template->buildContent($page, $language);

        return (new static($page, $language))
            ->setView($component)
            ->setPayload($component);
    }



    public static function getLocalization(Page $page, Language $language): PageLocalization {
        $localization = $page->getLocalization($language)
            ?? $page->getLocalization(App::getDefaultLanguage());

        if (is_null($localization)) {
            throw new RuntimeException("No localization found for page. Cannot display.");
        }

        return $localization;
    }

    public static function loadPayload(Payload $payload, PageLocalization $localization): void {
        $payload->setProperty(Head::PAYLOAD_TITLE, $localization->title);

        if (!is_null($meta = $localization->getMeta())) {
            $payload->addAllProperties(Head::PAYLOAD_HTML_META, [
                'description' => Html::escape($meta->description),
                'keywords' => Html::escape($meta->keywords),
                'og-title' => Html::escape($meta->ogTitle),
                'og-description' => Html::escape($meta->ogDescription),
            ]);
        }

        $payload->addAllProperties(Head::PAYLOAD_HTML_ELEMENTS, [
            self::createLocalizationApi($localization)
        ]);
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



    public function __construct(
        protected Page $page,
        protected Language $language,
        ?View $view = null,
        ?Payload $payload = null,
        ?Renderer $renderer = null,
    ) {
        parent::__construct($view, $payload, $renderer);
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }

    public function setPayload(Payload $payload): static {
        self::loadPayload($payload, self::getLocalization($this->page, $this->language));
        return parent::setPayload($payload);
    }



    public function getBreadCrumbs(): BreadCrumbs {
        return BreadCrumbs::from(static::createBreadCrumbs($this->page))
            ->setDelimitor(null);
    }

    public function hasContentAccess(): bool {
        return $this->page->hasAccess(
            User::fromRequest(App::getInstance()->getRequest()),
            Privilege::fromName(Privilege::READ)
        );
    }
}