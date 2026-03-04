<?php

namespace core\pages;

use components\pages\Wireframe\Wireframe;
use core\App;
use core\http\HttpCode;
use core\navigation\NavigationFactory;
use core\Singleton;
use core\view\Component;
use models\core\Navigation\NavigationFactoryRecord;
use models\core\Navigation\Slug;
use models\core\Page\Page;

class PageFactory implements NavigationFactory {
    use Singleton;



    public function getName(): string {
        return 'page';
    }

    public function createDestination(string $data): Component {
        $response = App::getInstance()->getResponse();

        if (is_null($page = Page::fromId(intval($data)))) {
            $response->sendMessage(
                'Page not found',
                HttpCode::CE_NOT_FOUND
            );
        }

        if (is_null($template = $page->getTemplate())) {
            $response->sendMessage(
                'Template is not set for the page',
                HttpCode::CE_CONFLICT
            );
        }

        $wireframe = new Wireframe($page);
        $wireframe->addContent(
            $template->buildContent($wireframe, $page)
        );

        return $wireframe;
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