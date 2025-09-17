<?php

namespace core\pages;

use components\core\Message\Message;
use core\navigation\NavigationFactory;
use core\Singleton;
use core\view\View;
use models\core\Navigation\NavigationFactoryRecord;
use models\core\Navigation\Slug;
use models\core\Page\Page;

class PageFactory implements NavigationFactory {
    use Singleton;



    public function getName(): string {
        return 'page';
    }

    public function createDestination(string $data): View {
        return new Message($data);
    }

    public function createSlug(int $languageId, int $contextId, string $slug, ?int $parentId, Page $page): Slug {
        $factory = NavigationFactoryRecord::fromName($this->getName(), create: true);

        $s = new Slug();

        $s->set([
            'slug' => $slug,
            'parentId' => $parentId,
            'languageId' => $languageId,
            'factoryId' => $factory->getId(),
            'data' => (string) $page->getId(),
            'contextId' => $contextId,
        ]);

        $s->save();
        return $s;
    }
}