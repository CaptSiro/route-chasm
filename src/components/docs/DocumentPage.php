<?php

namespace components\docs;

use components\core\HtmlHead\HtmlHead;
use components\core\PageMenu\Header;
use components\core\PageMenu\PageMenu;
use components\core\Search\HeaderSearch;
use components\core\Search\Search;
use components\core\WebPage\WebPage;
use components\pages\Article\Article;
use core\route\Path;
use core\view\ContainerContent;
use core\view\StringRenderer;
use models\core\Menu;

class DocumentPage extends ContainerContent {
    public const LEXICON_GROUP = Article::LEXICON_GROUP;



    protected WebPage $webPage;

    public function __construct(
        protected Docs $docs,
        protected ?string $directory = null
    ) {
        parent::__construct($this->webPage = new WebPage(head: $head = new HtmlHead()));
        $head->addElement(new StringRenderer(Search::createApi()));

        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function createHeader(): Header {
        return new Header(
            PageMenu::fromModelName(Menu::NAME_HEADER_DOCS),
            new HeaderSearch(
                url: Docs::getInstance()->createSearchUrl(),
                placeholder: "Search documents..."
            )
        );
    }

    protected function getEntries(): array {
        if (is_null($this->directory)) {
            return [];
        }

        $entries = [];
        foreach (scandir($this->directory) as $entry) {
            if ($entry === '..' || $entry === '.') {
                continue;
            }

            $entries[$entry] = Path::joinArray([$this->directory, $entry], DIRECTORY_SEPARATOR);
        }

        return $entries;
    }
}