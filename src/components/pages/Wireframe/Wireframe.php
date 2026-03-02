<?php

namespace components\pages\Wireframe;

use components\core\Html\Html;
use components\core\HtmlHead\HtmlHead;
use core\actions\Action;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\view\Component;
use core\view\Container;
use core\view\StringRenderer;
use core\view\View;
use DateTime;
use models\core\Language\Language;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;
use models\core\Privilege\Privilege;
use models\core\User\User;
use RuntimeException;

class Wireframe extends Component implements Container {
    public const LEXICON_GROUP = 'page';



    public static function createHtmlHead(LocalizedPage $localization): HtmlHead {
        $head = new HtmlHead(Html::escape($localization->title));

        $meta = $localization->getMeta();

        $head->addMetaNonEmpty('description', Html::escape($meta->description));
        $head->addMetaNonEmpty('keywords', Html::escape($meta->keywords));
        $head->addMetaNonEmpty('og-title', Html::escape($meta->ogTitle));
        $head->addMetaNonEmpty('og-description', Html::escape($meta->ogDescription));

        $head->addElement(new StringRenderer(self::createLocalizationApi($localization)));

        return $head;
    }

    public static function createLocalizationApi(LocalizedPage $localization): string {
        $releaseDate = new DateTime($localization->getPage()->getReleaseDate());

        return Html::wrapUnsafe(
            'script',
            json_encode([
                'title' => $localization->title,
                'description' => $localization->getMeta()->description,
                'releaseDate' => $localization
                    ->getLanguage()
                    ->getLocale()
                    ->formatDateTime($releaseDate->getTimestamp()),
            ]),
            [
                'type' => 'application/json',
                'id' => 'api-localization'
            ]
        );
    }



    protected Language $language;
    protected HtmlHead $head;
    protected LocalizedPage $localization;
    protected View $content;
    protected ?Action $action;
    protected bool $doAddHeader = true;
    protected bool $doAddFooter = true;

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

    public function setDoAddHeader(bool $doAddHeader): void {
        $this->doAddHeader = $doAddHeader;
    }

    public function setDoAddFooter(bool $doAddFooter): void {
        $this->doAddFooter = $doAddFooter;
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