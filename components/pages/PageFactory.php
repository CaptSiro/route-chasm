<?php

namespace components\pages;

use core\App;
use core\http\HttpCode;
use core\locale\LexiconUnit;
use core\navigation\NavigationFactory;
use core\Singleton;
use core\view\Component;
use models\Navigation\NavigationFactoryRecord;
use models\Navigation\Slug;
use models\Page\Page;

class PageFactory implements NavigationFactory {
    use Singleton, LexiconUnit;

    public const LEXICON_GROUP = 'page';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getName(): string {
        return 'page';
    }

    public function createDestination(string $data): Component {
        $messagePageNotFound = $this->tr('Page not found');
        $messageTemplateNotSet = $this->tr('Template is not set for the page');

        $response = App::getInstance()->getResponse();

        if (is_null($page = Page::fromId(intval($data)))) {
            $response->sendMessage($messagePageNotFound, HttpCode::CE_NOT_FOUND);
        }

        if (is_null($template = $page->getTemplate())) {
            $response->sendMessage($messageTemplateNotSet, HttpCode::CE_CONFLICT);
        }

        return Wireframe::fromTemplate(
            $template,
            $page,
            App::getInstance()
                ->getRequest()
                ->getLanguage()
        );
    }

    public function createSlug(int $languageId, int $contextId, string $slug, ?int $parentId, Page $page): Slug {
        $factory = NavigationFactoryRecord::fromName($this->getName(), create: true);

        $s = new Slug();

        $s->slug = $slug;
        $s->parentId = $parentId;
        $s->languageId = $languageId;
        $s->factoryId = $factory->id;
        $s->data = (string) $page->id;
        $s->contextId = $contextId;

        $s->save();
        return $s;
    }
}